<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestEncryptionController;
use App\Http\Controllers\MessageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-encryption', [TestEncryptionController::class, 'index']);

Route::middleware('auth:sanctum')->get('/chat', [MessageController::class, 'showChat']);

Route::middleware('auth:sanctum')->post('/send-message',[MessageController::class, 'send'])->name('send.message');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
