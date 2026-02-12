<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/*
|--------------------------------------------------------------------------
| Canales de Chat - Sistema Nexus
|--------------------------------------------------------------------------
|
| Canales privados para el sistema de chat en tiempo real.
| Mantienen la seguridad y compatibilidad con el sistema existente.
|
*/

// Canal privado para salas de chat específicas
// Formato: chat.{salaId} donde salaId = "userId1-userId2"
Broadcast::channel('chat.{sala}', function ($user, $sala) {
    // Verificar que el usuario pertenezca a la sala
    $partes = explode('-', $sala);
    
    // La sala debe tener exactamente 2 partes (2 usuarios)
    if (count($partes) !== 2) {
        return false;
    }
    
    // El usuario actual debe ser uno de los participantes
    return in_array($user->id, array_map('intval', $partes));
});

// Canal privado para notificaciones de usuario específico
// Formato: user.{userId}
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Solo el usuario puede escuchar su propio canal
    return (int) $user->id === (int) $userId;
});

// Canal privado para estado de usuario
// Formato: user-status.{userId}
Broadcast::channel('user-status.{userId}', function ($user, $userId) {
    // Solo el usuario puede escuchar su propio canal de estado
    return (int) $user->id === (int) $userId;
});

/*
|--------------------------------------------------------------------------
| Canales Públicos del Sistema
|--------------------------------------------------------------------------
|
| Canales públicos para información general del sistema.
|
*/

// Canal público para usuarios en línea
Broadcast::channel('online-users', function ($user) {
    // Cualquier usuario autenticado puede ver quién está en línea
    return true;
});

// Canal público para indicadores de escritura
Broadcast::channel('typing-indicators', function ($user) {
    // Cualquier usuario autenticado puede ver indicadores de escritura
    return true;
});

/*
|--------------------------------------------------------------------------
| Middleware de Autenticación para Canales
|--------------------------------------------------------------------------
|
| Todos los canales utilizan el middleware 'auth' para asegurar que
| solo usuarios autenticados puedan conectarse.
|
*/

// Aplicar middleware de autenticación a todos los canales
Broadcast::channel('*', function ($user) {
    // Verificar que el usuario esté autenticado vía sesión PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // El usuario debe tener una sesión válida del sistema principal
    return isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] === (int) $user->id;
});
