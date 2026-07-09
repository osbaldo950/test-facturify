<?php

namespace Database\Factories;

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thread>
 */
class ThreadFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(6),
            'created_by' => User::factory(),
            'status' => ThreadStatus::Open,
            'last_message_at' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ThreadStatus::Open,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ThreadStatus::Closed,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ThreadStatus::Pending,
        ]);
    }

    public function withMessages(int $count = 3): static
    {
        return $this->afterCreating(function (Thread $thread) use ($count) {
            $messages = Message::factory()
                ->count($count)
                ->for($thread)
                ->for($thread->creator)
                ->create();

            $thread->update([
                'last_message_at' => $messages->last()?->created_at,
            ]);
        });
    }
}
