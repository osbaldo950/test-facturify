<?php

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;

test('threads table can store a conversation with creator', function () {
    $user = User::factory()->create();

    $thread = Thread::factory()->for($user, 'creator')->create([
        'subject' => 'Consulta de facturación',
        'status' => ThreadStatus::Open,
    ]);

    expect($thread)->toBeInstanceOf(Thread::class)
        ->and($thread->subject)->toBe('Consulta de facturación')
        ->and($thread->status)->toBe(ThreadStatus::Open)
        ->and($thread->creator->is($user))->toBeTrue()
        ->and($user->threads)->toHaveCount(1);
});

test('messages belong to a thread and a user', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->for($user, 'creator')->create();

    $message = Message::factory()
        ->for($thread)
        ->for($user)
        ->create([
            'body' => 'Hola, necesito ayuda',
        ]);

    expect($message->thread->is($thread))->toBeTrue()
        ->and($message->user->is($user))->toBeTrue()
        ->and($thread->messages)->toHaveCount(1)
        ->and($user->messages)->toHaveCount(1)
        ->and($message->is_read)->toBeFalse();
});

test('thread factory can create messages and update last_message_at', function () {
    $thread = Thread::factory()->withMessages(3)->create();

    expect($thread->messages)->toHaveCount(3)
        ->and($thread->fresh()->last_message_at)->not->toBeNull();
});

test('message unread scope filters correctly', function () {
    $thread = Thread::factory()->create();

    Message::factory()->for($thread)->unread()->count(2)->create();
    Message::factory()->for($thread)->read()->count(1)->create();

    expect(Message::query()->unread()->count())->toBe(2)
        ->and(Message::query()->read()->count())->toBe(1);
});

test('thread search scope filters by subject', function () {
    Thread::factory()->create(['subject' => 'Problema con factura']);
    Thread::factory()->create(['subject' => 'Consulta de envío']);

    $results = Thread::query()->search('factura')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->subject)->toBe('Problema con factura');
});

test('thread status scopes filter correctly', function () {
    Thread::factory()->open()->count(2)->create();
    Thread::factory()->closed()->count(1)->create();

    expect(Thread::query()->open()->count())->toBe(2)
        ->and(Thread::query()->closed()->count())->toBe(1);
});
