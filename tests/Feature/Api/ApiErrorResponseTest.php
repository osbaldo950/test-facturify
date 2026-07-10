<?php

use App\Enums\ThreadStatus;
use App\Models\Thread;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

function errorFormatTokenFor(User $user): string
{
    return JWTAuth::fromUser($user);
}

test('unauthenticated api requests return standardized 401 payload', function () {
    $this->getJson('/api/threads')
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'No autenticado.')
        ->assertJsonMissingPath('data');
});

test('validation errors return standardized 422 payload', function () {
    $user = User::factory()->create();

    $response = $this->withToken(errorFormatTokenFor($user))
        ->postJson('/api/threads', []);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Los datos enviados no son válidos.')
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => ['subject', 'body'],
        ]);
});

test('missing resources return standardized 404 payload', function () {
    $user = User::factory()->create();

    $this->withToken(errorFormatTokenFor($user))
        ->getJson('/api/threads/999999')
        ->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Recurso no encontrado.');
});

test('domain exceptions return standardized error payload', function () {
    $user = User::factory()->create();
    $thread = Thread::factory()->closed()->for($user, 'creator')->create([
        'status' => ThreadStatus::Closed,
    ]);

    $this->withToken(errorFormatTokenFor($user))
        ->postJson('/api/threads/'.$thread->id.'/messages', [
            'body' => 'Intento de respuesta',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'No se puede responder una conversación cerrada.');
});

test('successful responses keep standardized success payload', function () {
    $user = User::factory()->create();

    $this->withToken(errorFormatTokenFor($user))
        ->getJson('/api/auth/me')
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'name', 'email'],
        ]);
});
