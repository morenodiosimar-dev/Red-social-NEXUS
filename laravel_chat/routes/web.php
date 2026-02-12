<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - Chat System
|--------------------------------------------------------------------------
|
| Rutas principales del sistema de chat compatibles con el frontend existente.
| Mantienen la misma estructura que el sistema Node.js original.
|
*/

// Rutas de autenticación compatibles con el sistema existente
Route::post('/chat/devuelve', [ChatController::class, 'getCurrentUser']);
Route::get('/chat/usuarios', [ChatController::class, 'getUsers']);

// Rutas principales del chat
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/contactos', [ChatController::class, 'getContacts'])->name('chat.contacts');
Route::post('/chat/mensaje', [ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/mensajes/{sala}', [ChatController::class, 'getMessages'])->name('chat.messages');
Route::post('/chat/typing', [ChatController::class, 'sendTyping'])->name('chat.typing');
Route::get('/chat/online', [ChatController::class, 'getOnlineUsers'])->name('chat.online');
Route::post('/chat/logout', [ChatController::class, 'logout'])->name('chat.logout');

// Ruta para servir el frontend del chat (compatibilidad con sistema existente)
Route::get('/chat/frontend', function () {
    return view('chat.index');
});

// Ruta de bienvenida por defecto
// Ruta de bienvenida por defecto - Redirigir al chat o mostrar interfaz
Route::get('/', function () {
    return redirect('/chat');
});
