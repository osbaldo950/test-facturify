<?php

namespace App\Exceptions\Api;

use App\Http\Responses\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CannotReplyToClosedThreadException extends Exception
{
    public function __construct()
    {
        parent::__construct('No se puede responder una conversación cerrada.', 422);
    }

    public function render(Request $request): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), 422);
    }
}
