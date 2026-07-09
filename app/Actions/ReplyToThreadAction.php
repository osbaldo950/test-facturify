<?php

namespace App\Actions;

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReplyToThreadAction
{
    /**
     * @param  array{body: string}  $data
     */
    public function handle(Thread $thread, User $user, array $data): Message
    {
        if ($thread->status === ThreadStatus::Closed) {
            throw new HttpException(422, 'No se puede responder una conversación cerrada.');
        }

        return DB::transaction(function () use ($thread, $user, $data): Message {
            $message = Message::query()->create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'body' => $data['body'],
                'is_read' => false,
                'read_at' => null,
            ]);

            $thread->update([
                'last_message_at' => $message->created_at,
            ]);

            return $message->load('user');
        });
    }
}
