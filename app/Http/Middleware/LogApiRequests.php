<?php

namespace App\Http\Middleware;

use App\Core\Application\Services\LoggingService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

        $this->loggingService->logApiRequest(
            [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
            ],
            [
                'status' => $response->getStatusCode(),
            ],
            $duration
        );

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
}
