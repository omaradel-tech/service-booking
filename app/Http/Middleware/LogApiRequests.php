<?php

namespace App\Http\Middleware;

use App\Core\Application\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function __construct(
        private LoggingService $loggingService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Skip logging for health checks and internal routes
        if ($this->shouldSkipLogging($request)) {
            return $response;
        }

        $requestData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
        ];

        // Add request body for POST/PUT/PATCH requests (with redaction)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $requestData['body'] = $this->redactSensitiveData($request->getContent());
        }

        $responseData = [
            'status' => $response->getStatusCode(),
        ];

        $this->loggingService->logApiRequest($requestData, $responseData, $duration);

        return $response;
    }

    /**
     * Determine if the request should be skipped from logging.
     */
    private function shouldSkipLogging(Request $request): bool
    {
        // Skip health checks
        if ($request->is('health') || $request->is('ping')) {
            return true;
        }

        // Skip log-viewer routes
        if ($request->is('log-viewer*')) {
            return true;
        }

        // Skip documentation routes
        if ($request->is('docs*') || $request->is('scribe*')) {
            return true;
        }

        return false;
    }

    /**
     * Redact sensitive data from request body.
     */
    private function redactSensitiveData(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // Limit body size to 4KB to prevent log bloat
        if (strlen($content) > 4096) {
            $content = substr($content, 0, 4096) . '... [truncated]';
        }

        // Define sensitive fields to redact
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'authorization',
            'api_key',
            'secret',
            'key',
            'card_number',
            'card_cvv',
            'card_expires',
            'cvv',
            'ssn',
            'social_security_number',
        ];

        // Try to decode as JSON for field-level redaction
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $decoded = $this->redactArray($decoded, $sensitiveFields);
            return json_encode($decoded);
        }

        // Fallback: redact common patterns in raw content
        foreach ($sensitiveFields as $field) {
            $content = preg_replace(
                "/(\"$field\"\s*:\s*\")(.*?)(\"|\")/",
                "\"$field\": \"[REDACTED]\"",
                $content
            );
        }

        return $content;
    }

    /**
     * Recursively redact sensitive data from arrays.
     */
    private function redactArray(array $data, array $sensitiveFields): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->redactArray($value, $sensitiveFields);
            } elseif (is_string($value)) {
                // Check if key matches any sensitive field
                foreach ($sensitiveFields as $field) {
                    if (stripos($key, $field) !== false) {
                        $data[$key] = '[REDACTED]';
                        break;
                    }
                }
            }
        }

        return $data;
    }
}
