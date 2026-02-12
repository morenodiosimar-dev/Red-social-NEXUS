<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cargar rutas de broadcasting para el chat
        Broadcast::routes();

        // Middleware para autenticación de canales
        // Asegura compatibilidad con sesiones PHP del sistema principal
        Broadcast::routes(['middleware' => ['web']]);

        /*
        |--------------------------------------------------------------------------
        | Autenticación de Canales de Chat
        |--------------------------------------------------------------------------
        |
        | Define las reglas de autorización para los canales del chat.
        | Mantenemos la misma lógica de seguridad que el sistema Node.js.
        |
        */

        // Canal de sala de chat privado
        Broadcast::channel('chat.{sala}', function ($user, $sala) {
            // Verificar que el usuario esté autenticado vía sesión PHP
            if (!$this->validateSessionUser($user)) {
                return false;
            }

            // La sala debe tener formato userId1-userId2
            $partes = explode('-', $sala);
            if (count($partes) !== 2) {
                return false;
            }

            // El usuario debe ser uno de los participantes
            $userId = (int) $user->id;
            $participantes = array_map('intval', $partes);
            
            return in_array($userId, $participantes);
        });

        // Canal de notificaciones privadas de usuario
        Broadcast::channel('user.{userId}', function ($user, $userId) {
            if (!$this->validateSessionUser($user)) {
                return false;
            }
            
            return (int) $user->id === (int) $userId;
        });

        // Canal de estado de usuario
        Broadcast::channel('user-status.{userId}', function ($user, $userId) {
            if (!$this->validateSessionUser($user)) {
                return false;
            }
            
            return (int) $user->id === (int) $userId;
        });

        // Canales públicos (acceso para usuarios autenticados)
        Broadcast::channel('online-users', function ($user) {
            return $this->validateSessionUser($user);
        });

        Broadcast::channel('typing-indicators', function ($user) {
            return $this->validateSessionUser($user);
        });
    }

    /**
     * Validar que el usuario tenga una sesión PHP válida
     * del sistema principal (compatibilidad con sistema existente)
     */
    private function validateSessionUser($user): bool
    {
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar que exista sesión de usuario principal
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }

        // Verificar que el ID de sesión coincida con el usuario actual
        return (int) $_SESSION['usuario_id'] === (int) $user->id;
    }
}
