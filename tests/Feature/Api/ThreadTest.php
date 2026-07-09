<?php

use App\Enums\ThreadStatus;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function apiTokenFor(User $user): string
{
    return JWTAuth::fromUser($user);
}

test('threads index requires authentication', function () {
    $this->getJson('/api/threads')->assertUnauthorized();
});

test('threads index returns paginated conversations', function () {
    $user = User::factory()->create();
    Thread::factory()->count(3)->for($user, 'creator')->create();

    $response = $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads');

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 3)
        ->assertJsonCount(3, 'data.threads');
});

test('threads index can search by subject', function () {
    $user = User::factory()->create();
    Thread::factory()->for($user, 'creator')->create(['subject' => 'Problema con factura']);
    Thread::factory()->for($user, 'creator')->create(['subject' => 'Consulta de envío']);

    $response = $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads?search=factura');

    $response->assertSuccessful()
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.threads.0.subject', 'Problema con factura');
});

test('threads index can filter by status', function () {
    $user = User::factory()->create();
    Thread::factory()->open()->for($user, 'creator')->count(2)->create();
    Thread::factory()->closed()->for($user, 'creator')->count(1)->create();

    $response = $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads?status=closed');

    $response->assertSuccessful()
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.threads.0.status', ThreadStatus::Closed->value);
});

test('threads show returns thread detail with messages', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->for($user, 'creator')->create([
        'subject' => 'Detalle de hilo',
    ]);

    Message::factory()->for($thread)->for($user)->count(2)->create();

    $response = $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads/'.$thread->id);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.subject', 'Detalle de hilo')
        ->assertJsonPath('data.creator.email', $user->email)
        ->assertJsonCount(2, 'data.messages');
});

test('threads show returns not found for missing thread', function () {
    $user = User::factory()->create();

    $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads/999999')
        ->assertNotFound();
});

test('threads index rejects invalid status', function () {
    $user = User::factory()->create();

    $this->withToken(apiTokenFor($user))
        ->getJson('/api/threads?status=invalid')
        ->assertUnprocessable();
});
