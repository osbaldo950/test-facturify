<?php

namespace App\Actions;

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateThreadAction
{
    /**
     * @param  array{subject: string, body: string}  $data
     */
    public function handle(User $user, array $data): Thread
    {
        return DB::transaction(function () use ($user, $data): Thread {
            $thread = Thread::query()->create([
                'subject' => $data['subject'],
                'created_by' => $user->id,
                'status' => ThreadStatus::Open,
                'last_message_at' => now(),
            ]);

            Message::query()->create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'body' => $data['body'],
                'is_read' => true,
                'read_at' => now(),
            ]);

            return $thread->load([
                'creator',
                'messages' => fn ($query) => $query->with('user')->oldest(),
            ]);
        });
    }
}
