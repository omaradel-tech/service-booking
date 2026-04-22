<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\DomainException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api([
            \App\Http\Middleware\LogApiRequests::class,
        ]);

        $middleware->alias([
            'idempotent' => \App\Http\Middleware\IdempotencyKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (DomainException $e) {
            return $e->render();
        });

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'details' => $e->errors(),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication required.',
                ],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'This action is unauthorized.',
                ],
            ], 403);
        });

        $exceptions->render(function (ThrottleRequestsException $e) {
            return response()->json([
                'error' => [
                    'code' => 'TOO_MANY_REQUESTS',
                    'message' => 'Too many requests. Please try again later.',
                    'details' => [
                        'retry_after' => $e->retryAfter ?? 60,
                    ],
                ],
            ], 429);
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Resource not found.',
                    'details' => [
                        'model' => class_basename($e->getModel()),
                    ],
                ],
            ], 404);
        });

        $exceptions->render(function (Throwable $e) {
            Log::channel('security')->error('Unhandled exception', [
                'exception' => $e,
                'url' => request()->fullUrl(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => config('app.debug') ? $e->getMessage() : 'An internal error occurred.',
                    'details' => config('app.debug') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ] : null,
                ],
            ], 500);
        });
    })->create();
