<?php

use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function notificationTokenFor(User $user): string
{
    return JWTAuth::fromUser($user);
}

test('unread notifications require authentication', function () {
    $this->getJson('/api/notifications/unread')->assertUnauthorized();
});

test('unread notifications return messages from others in related threads', function () {
    $owner = User::factory()->create();
    $agent = User::factory()->create();

    $thread = Thread::factory()->for($owner, 'creator')->create();

    Message::factory()->for($thread)->for($owner)->create([
        'body' => 'Mensaje del dueño',
        'is_read' => true,
        'read_at' => now(),
    ]);

    $unread = Message::factory()->for($thread)->for($agent)->unread()->create([
        'body' => 'Respuesta del agente',
    ]);

    Message::factory()->for($thread)->for($agent)->read()->create([
        'body' => 'Mensaje ya leído',
    ]);

    $otherOwner = User::factory()->create();
    $otherThread = Thread::factory()->for($otherOwner, 'creator')->create();
    Message::factory()->for($otherThread)->for($agent)->unread()->create([
        'body' => 'No debería verse',
    ]);

    $response = $this->withToken(notificationTokenFor($owner))
        ->getJson('/api/notifications/unread');

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.messages.0.id', $unread->id)
        ->assertJsonPath('data.messages.0.body', 'Respuesta del agente');
});

test('unread notifications exclude own unread messages', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->for($user, 'creator')->create();

    Message::factory()->for($thread)->for($user)->unread()->create([
        'body' => 'Mi propio mensaje no leído',
    ]);

    $response = $this->withToken(notificationTokenFor($user))
        ->getJson('/api/notifications/unread');

    $response->assertSuccessful()
        ->assertJsonPath('data.meta.total', 0)
        ->assertJsonCount(0, 'data.messages');
});
