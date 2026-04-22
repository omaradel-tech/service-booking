<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Http\Responses\ApiResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Handle API exceptions and return proper JSON responses.
     */
    protected function handleApiException(Throwable $exception): JsonResponse
    {
        // Validation exceptions
        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                'VALIDATION_ERROR',
                'The given data was invalid.',
                $exception->errors(),
                422
            );
        }

        // Authorization exceptions
        if ($exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException) {
            return ApiResponse::error(
                'UNAUTHORIZED',
                'This action is unauthorized.',
                null,
                403
            );
        }

        // Model not found exceptions
        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return ApiResponse::error(
                'NOT_FOUND',
                'Resource not found.',
                null,
                404
            );
        }

        // Default error response
        return ApiResponse::error(
            'INTERNAL_ERROR',
            'An internal error occurred.',
            config('app.debug') ? [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ] : null,
            500
        );
    }
}
