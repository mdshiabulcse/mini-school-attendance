<?php
// app/Exceptions/Handler.php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Handle API exceptions and return consistent JSON responses.
     */
    private function handleApiException(Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $response = [
            'success' => false,
            'message' => $this->getExceptionMessage($exception, $statusCode),
            'error_code' => $this->getErrorCode($exception),
        ];

        // Add validation errors if it's a validation exception
        if ($exception instanceof ValidationException) {
            $response['errors'] = $exception->errors();
        }

        // Add debug information in local environment
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get appropriate HTTP status code for exception.
     */
    private function getStatusCode(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof AuthenticationException => Response::HTTP_UNAUTHORIZED,
            $exception instanceof ModelNotFoundException,
                $exception instanceof NotFoundHttpException => Response::HTTP_NOT_FOUND,
            $exception instanceof MethodNotAllowedHttpException => Response::HTTP_METHOD_NOT_ALLOWED,
            $exception instanceof ValidationException => Response::HTTP_UNPROCESSABLE_ENTITY,
            default => method_exists($exception, 'getStatusCode')
                ? $exception->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    /**
     * Get user-friendly exception message.
     */
    private function getExceptionMessage(Throwable $exception, int $statusCode): string
    {
        return match (true) {
            $exception instanceof AuthenticationException => 'Unauthenticated. Please log in.',
            $exception instanceof ModelNotFoundException => 'The requested resource was not found.',
            $exception instanceof NotFoundHttpException => 'The requested endpoint was not found.',
            $exception instanceof MethodNotAllowedHttpException => 'This method is not allowed for the requested endpoint.',
            $exception instanceof ValidationException => 'The given data was invalid.',
            $statusCode === Response::HTTP_INTERNAL_SERVER_ERROR => 'An internal server error occurred. Please try again later.',
            default => $exception->getMessage() ?: 'An error occurred.',
        };
    }

    /**
     * Get error code for exception.
     */
    private function getErrorCode(Throwable $exception): string
    {
        return match (true) {
            $exception instanceof AuthenticationException => 'UNAUTHENTICATED',
            $exception instanceof ModelNotFoundException => 'RESOURCE_NOT_FOUND',
            $exception instanceof NotFoundHttpException => 'ENDPOINT_NOT_FOUND',
            $exception instanceof MethodNotAllowedHttpException => 'METHOD_NOT_ALLOWED',
            $exception instanceof ValidationException => 'VALIDATION_ERROR',
            default => 'INTERNAL_ERROR',
        };
    }
}
