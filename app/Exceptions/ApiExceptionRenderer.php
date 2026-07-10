<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function shouldRender(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! self::shouldRender($request)) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                'Los datos enviados no son válidos.',
                422,
                $e->errors()
            );
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error('No autenticado.', 401);
        }

        if ($e instanceof AuthorizationException) {
            return ApiResponse::error('No tienes permisos para realizar esta acción.', 403);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return ApiResponse::error('Recurso no encontrado.', 404);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() !== ''
                ? $e->getMessage()
                : self::defaultMessageForStatus($status);

            return ApiResponse::error($message, $status);
        }

        // Domain exceptions with their own render() method.
        if (method_exists($e, 'render')) {
            return null;
        }

        if (! config('app.debug')) {
            return ApiResponse::error('Error interno del servidor.', 500);
        }

        return null;
    }

    private static function defaultMessageForStatus(int $status): string
    {
        return match ($status) {
            401 => 'No autenticado.',
            403 => 'No tienes permisos para realizar esta acción.',
            404 => 'Recurso no encontrado.',
            422 => 'Los datos enviados no son válidos.',
            429 => 'Demasiadas solicitudes. Intenta más tarde.',
            default => 'Ha ocurrido un error.',
        };
    }
}
