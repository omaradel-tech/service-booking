<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class IdempotencyKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $idempotencyKey = $request->header('Idempotency-Key');
        
        // Only apply to POST, PUT, PATCH requests
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // If no idempotency key provided, proceed normally
        if (!$idempotencyKey) {
            return $next($request);
        }

        // Generate cache key based on user, idempotency key, method, and path
        $userId = $request->user()?->id ?? $request->ip();
        $cacheKey = "idempotency:{$userId}:{$idempotencyKey}:{$request->method()}:{$request->path()}";
        
        // Include request body in hash for POST requests
        if ($request->isMethod('POST')) {
            $bodyHash = hash('sha256', $request->getContent());
            $cacheKey .= ":{$bodyHash}";
        }

        // Check if we have a cached response
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            Log::channel('api')->info('Idempotent request replayed', [
                'cache_key' => $cacheKey,
                'user_id' => $userId,
                'method' => $request->method(),
                'path' => $request->path(),
            ]);

            return $this->recreateResponse($cachedResponse);
        }

        // Process the request
        $response = $next($request);

        // Only cache successful responses (2xx status codes)
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            // Cache the response for 24 hours
            $cacheData = [
                'status' => $response->getStatusCode(),
                'headers' => $response->headers->all(),
                'content' => $response->getContent(),
            ];

            Cache::put($cacheKey, $cacheData, 86400); // 24 hours

            // Add response header to indicate idempotency was processed
            $response->headers->set('Idempotency-Processed', 'true');
        }

        return $response;
    }

    /**
     * Recreate a response from cached data.
     */
    private function recreateResponse(array $cachedData): SymfonyResponse
    {
        $response = new Response($cachedData['content'], $cachedData['status']);

        // Restore headers
        foreach ($cachedData['headers'] as $name => $values) {
            $response->headers->set($name, $values);
        }

        return $response;
    }
}
