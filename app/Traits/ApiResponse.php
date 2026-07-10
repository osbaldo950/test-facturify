<?php

namespace App\Traits;

use App\Http\Responses\ApiResponse as ApiJsonResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return ApiJsonResponse::success($data, $message, $code);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    protected function errorResponse(string $message, int $code = 400, ?array $errors = null): JsonResponse
    {
        return ApiJsonResponse::error($message, $code, $errors);
    }
}
