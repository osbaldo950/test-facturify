<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MessageResource;
use App\Models\Message;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function unread(Request $request): JsonResponse
    {
        $user = $request->user('api');
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $messages = Message::query()
            ->with(['user', 'thread.creator'])
            ->where('is_read', false)
            ->where('user_id', '!=', $user->id)
            ->whereHas('thread', function ($query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhereHas('messages', fn ($messages) => $messages->where('user_id', $user->id));
            })
            ->latest()
            ->paginate($perPage);

        return $this->successResponse([
            'messages' => MessageResource::collection($messages)->resolve(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ], 'Mensajes no leídos obtenidos correctamente.');
    }
}
