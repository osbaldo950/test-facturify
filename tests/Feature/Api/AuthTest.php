<?php

use App\Models\User;

test('api login returns jwt token with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'api-user@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Inicio de sesión exitoso.')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'name', 'email'],
            ],
        ]);
});

test('api login returns unauthorized with invalid credentials', function () {
    User::factory()->create([
        'email' => 'api-user@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'api-user@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Credenciales inválidas.');
});

test('api me returns authenticated user with valid token', function () {
    $user = User::factory()->create([
        'email' => 'api-user@example.com',
        'password' => 'password',
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $token = $loginResponse->json('data.access_token');

    $response = $this->getJson('/api/auth/me', [
        'Authorization' => 'Bearer '.$token,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', $user->email);
});

test('api me returns unauthorized without token', function () {
    $this->getJson('/api/auth/me')->assertUnauthorized();
});
