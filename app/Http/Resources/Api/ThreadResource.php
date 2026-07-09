<?php

namespace App\Http\Resources\Api;

use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Thread
 */
class ThreadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'messages_count' => $this->whenCounted('messages'),
        ];
    }
}
