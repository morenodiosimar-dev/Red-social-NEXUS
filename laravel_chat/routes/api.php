<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatAPIController;

/*
|--------------------------------------------------------------------------
| API Routes - Chat System
|--------------------------------------------------------------------------
|
| Aquí se definen todas las rutas API para el sistema de chat.
| Estas rutas son compatibles con el frontend existente y mantienen
| la misma estructura de endpoints que el sistema Node.js original.
|
*/

// Middleware para asegurar que las rutas API sean accesibles
Route::middleware('web')->group(function () {

    // Endpoint principal de compatibilidad (reemplaza a http://localhost/chat/devuelve.php)
    Route::post('/chat/devuelve', [ChatAPIController::class, 'getCurrentUser']);
    
    // Endpoint de usuarios (reemplaza a http://localhost/chat/usuarios.php)
    Route::get('/chat/usuarios', [ChatAPIController::class, 'getUsers']);
    
    // Endpoints principales del chat
    Route::get('/chat/contactos', [ChatAPIController::class, 'getContacts']);
    Route::post('/chat/mensaje', [ChatAPIController::class, 'sendMessage']);
    Route::get('/chat/mensajes/{sala}', [ChatAPIController::class, 'getMessages']);
    Route::post('/chat/typing', [ChatAPIController::class, 'sendTyping']);
    Route::get('/chat/online', [ChatAPIController::class, 'getOnlineUsers']);
    Route::post('/chat/logout', [ChatAPIController::class, 'logout']);

});

/*
|--------------------------------------------------------------------------
| Rutas de Broadcasting (para Laravel Echo)
|--------------------------------------------------------------------------
|
| Estas rutas son utilizadas por Laravel Echo para la autenticación
| de canales privados de broadcasting.
|
*/

Route::middleware('web')->group(function () {
    Route::get('/broadcasting/auth', function () {
        return response()->json(['message' => 'Broadcasting auth endpoint']);
    });
});
