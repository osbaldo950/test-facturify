<?php

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function messagingTokenFor(User $user): string
{
    return JWTAuth::fromUser($user);
}

test('creating a thread requires authentication', function () {
    $this->postJson('/api/threads', [
        'subject' => 'Nueva consulta',
        'body' => 'Hola, necesito ayuda',
    ])->assertUnauthorized();
});

test('authenticated user can create a thread with first message', function () {
    $user = User::factory()->create();

    $response = $this->withToken(messagingTokenFor($user))
        ->postJson('/api/threads', [
            'subject' => 'Consulta de facturación',
            'body' => 'Tengo un problema con mi factura',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.subject', 'Consulta de facturación')
        ->assertJsonPath('data.status', ThreadStatus::Open->value)
        ->assertJsonPath('data.creator.email', $user->email)
        ->assertJsonCount(1, 'data.messages')
        ->assertJsonPath('data.messages.0.body', 'Tengo un problema con mi factura');

    $this->assertDatabaseHas('threads', [
        'subject' => 'Consulta de facturación',
        'created_by' => $user->id,
        'status' => ThreadStatus::Open->value,
    ]);

    $this->assertDatabaseHas('messages', [
        'user_id' => $user->id,
        'body' => 'Tengo un problema con mi factura',
        'is_read' => true,
    ]);
});

test('creating a thread requires subject and body', function () {
    $user = User::factory()->create();

    $this->withToken(messagingTokenFor($user))
        ->postJson('/api/threads', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['subject', 'body']);
});

test('authenticated user can reply to a thread', function () {
    $creator = User::factory()->create();
    $replier = User::factory()->create();
    $thread = Thread::factory()->for($creator, 'creator')->create();

    Message::factory()->for($thread)->for($creator)->create([
        'body' => 'Mensaje inicial',
        'is_read' => true,
        'read_at' => now(),
    ]);

    $response = $this->withToken(messagingTokenFor($replier))
        ->postJson('/api/threads/'.$thread->id.'/messages', [
            'body' => 'Esta es mi respuesta',
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.body', 'Esta es mi respuesta')
        ->assertJsonPath('data.is_read', false)
        ->assertJsonPath('data.user.email', $replier->email);

    $this->assertDatabaseHas('messages', [
        'thread_id' => $thread->id,
        'user_id' => $replier->id,
        'body' => 'Esta es mi respuesta',
        'is_read' => false,
    ]);

    expect($thread->fresh()->last_message_at)->not->toBeNull();
});

test('replying requires a message body', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->for($user, 'creator')->create();

    $this->withToken(messagingTokenFor($user))
        ->postJson('/api/threads/'.$thread->id.'/messages', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['body']);
});

test('cannot reply to a closed thread', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->closed()->for($user, 'creator')->create();

    $this->withToken(messagingTokenFor($user))
        ->postJson('/api/threads/'.$thread->id.'/messages', [
            'body' => 'Intento de respuesta',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'No se puede responder una conversación cerrada.');
});

test('replying to a missing thread returns not found', function () {
    $user = User::factory()->create();

    $this->withToken(messagingTokenFor($user))
        ->postJson('/api/threads/999999/messages', [
            'body' => 'Hola',
        ])
        ->assertNotFound();
});
