<?php

namespace App\Http\Controllers\Api;

use App\Actions\ReplyToThreadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Message\StoreMessageRequest;
use App\Http\Resources\Api\MessageResource;
use App\Models\Thread;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    use ApiResponse;

    public function store(
        StoreMessageRequest $request,
        Thread $thread,
        ReplyToThreadAction $replyToThread
    ): JsonResponse {
        $message = $replyToThread->handle(
            $thread,
            $request->user('api'),
            $request->validated()
        );

        return $this->successResponse(
            new MessageResource($message),
            'Mensaje enviado exitosamente.',
            201
        );
    }
}
