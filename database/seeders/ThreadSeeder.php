<?php

namespace Database\Seeders;

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    /**
     * Seed threads and messages for API testing.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->count() < 2) {
            $users = User::factory()->count(3)->create();
        }

        $creator = $users->first();
        $participants = $users->skip(1)->values();

        Thread::factory()
            ->count(5)
            ->open()
            ->for($creator, 'creator')
            ->create()
            ->each(function (Thread $thread) use ($creator, $participants): void {
                $firstMessage = Message::factory()->for($thread)->for($creator)->create([
                    'body' => 'Mensaje inicial: '.$thread->subject,
                    'is_read' => true,
                    'read_at' => now(),
                ]);

                $replies = collect();

                foreach ($participants->take(2) as $participant) {
                    $replies->push(
                        Message::factory()->for($thread)->for($participant)->unread()->create()
                    );
                }

                $thread->update([
                    'last_message_at' => $replies->last()?->created_at ?? $firstMessage->created_at,
                ]);
            });

        Thread::factory()
            ->count(2)
            ->closed()
            ->for($creator, 'creator')
            ->withMessages(2)
            ->create();

        Thread::factory()
            ->pending()
            ->for($creator, 'creator')
            ->create([
                'subject' => 'Conversación pendiente de revisión',
                'status' => ThreadStatus::Pending,
            ]);
    }
}
