<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ThreadController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('threads', [ThreadController::class, 'index']);
    Route::get('threads/{thread}', [ThreadController::class, 'show']);
});
