<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestEncryptionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::get('/test-encryption', [TestEncryptionController::class, 'index']);
Route::post('/rsa', [MessageController::class, 'testeRSA']);

Route::middleware('auth:sanctum')->get('/chat', [MessageController::class, 'showChat']);

Route::middleware('auth:sanctum')->post('/send-message',[MessageController::class, 'send'])->name('send.message');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Modifique esta rota para usar o UserController
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    Route::get('/chat/{id}', [MessageController::class, 'showChat'])->name('chat');
    Route::post('/chatMessage/{receiverId}', [MessageController::class, 'getMessages'])->name('chatMessage');
    Route::get('/download-file/{messageId}', [MessageController::class, 'downloadDecryptedFile'])->name('downloadFile');
});