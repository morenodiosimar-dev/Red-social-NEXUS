<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SharedAuthController extends Controller
{
    /**
     * Verificar estado de autenticación compartida
     * Endpoint para compatibilidad con sistema principal
     */
    public function checkAuth(Request $request)
    {
        try {
            // Iniciar sesión PHP si no está activa
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verificar si existe sesión del sistema principal
            if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre'])) {
                $user = User::find($_SESSION['usuario_id']);
                
                if ($user) {
                    // Actualizar última actividad
                    $_SESSION['ultima_actividad'] = now()->timestamp;
                    
                    return response()->json([
                        'authenticated' => true,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->nombre_completo,
                            'email' => $user->correo,
                            'avatar' => $user->foto_perfil ?: 'default.png'
                        ],
                        'session_data' => [
                            'usuario_id' => $_SESSION['usuario_id'],
                            'nombre' => $_SESSION['nombre'],
                            'apellido' => $_SESSION['apellido'] ?? '',
                            'ultima_actividad' => $_SESSION['ultima_actividad'] ?? now()->timestamp
                        ]
                    ]);
                }
            }

            return response()->json([
                'authenticated' => false,
                'message' => 'No hay sesión activa del sistema principal'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'authenticated' => false,
                'error' => 'Error verificando autenticación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar usuario del sistema principal con Laravel
     */
    public function syncUser(Request $request)
    {
        try {
            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verificar que exista sesión del sistema principal
            if (!isset($_SESSION['usuario_id'])) {
                return response()->json(['error' => 'No hay sesión del sistema principal'], 401);
            }

            $userId = $_SESSION['usuario_id'];
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado en Laravel'], 404);
            }

            // Sincronizar datos si es necesario
            $needsUpdate = false;
            $updateData = [];

            if (isset($_SESSION['nombre']) && $_SESSION['nombre'] !== $user->nombre) {
                $updateData['nombre'] = $_SESSION['nombre'];
                $needsUpdate = true;
            }

            if (isset($_SESSION['apellido']) && $_SESSION['apellido'] !== $user->apellido) {
                $updateData['apellido'] = $_SESSION['apellido'];
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $user->update($updateData);
            }

            // Autenticar en Laravel
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuario sincronizado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->nombre_completo,
                    'email' => $user->correo,
                    'avatar' => $user->foto_perfil ?: 'default.png'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error sincronizando usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint de login compartido (compatibilidad)
     */
    public function sharedLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Buscar usuario en la base de datos
            $user = User::where('correo', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->contraseña)) {
                return response()->json(['error' => 'Credenciales inválidas'], 401);
            }

            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Establecer variables de sesión del sistema principal
            $_SESSION['usuario_id'] = $user->id;
            $_SESSION['nombre'] = $user->nombre;
            $_SESSION['apellido'] = $user->apellido;
            $_SESSION['correo'] = $user->correo;
            $_SESSION['ultima_actividad'] = now()->timestamp;

            // Autenticar en Laravel
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->nombre_completo,
                    'email' => $user->correo,
                    'avatar' => $user->foto_perfil ?: 'default.png'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en login: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout compartido
     */
    public function sharedLogout(Request $request)
    {
        try {
            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Guardar ID de usuario antes de destruir sesión
            $userId = $_SESSION['usuario_id'] ?? null;

            // Destruir sesión PHP
            session_destroy();

            // Cerrar sesión en Laravel
            Auth::logout();

            // Invalidar cookies de sesión
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Si hay usuario, marcar como desconectado en el chat
            if ($userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->markAsOffline();
                    // Evento de broadcast para logout
                    broadcast(new \App\Events\UserOnline($user, false));
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en logout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar token de sesión para API
     */
    public function validateSession(Request $request)
    {
        try {
            $token = $request->get('token') ?: $request->bearerToken();

            if (!$token) {
                return response()->json(['valid' => false, 'error' => 'No token provided'], 401);
            }

            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verificar token en sesión
            if (!isset($_SESSION['api_token']) || $_SESSION['api_token'] !== $token) {
                return response()->json(['valid' => false, 'error' => 'Invalid token'], 401);
            }

            // Verificar que la sesión no haya expirado
            $inactividad = now()->timestamp - ($_SESSION['ultima_actividad'] ?? 0);
            $tiempoMaximo = config('session.lifetime', 120) * 60;

            if ($inactividad > $tiempoMaximo) {
                return response()->json(['valid' => false, 'error' => 'Session expired'], 401);
            }

            return response()->json([
                'valid' => true,
                'user_id' => $_SESSION['usuario_id'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'Error validating session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar token de API para sesión actual
     */
    public function generateApiToken(Request $request)
    {
        try {
            // Iniciar sesión PHP
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verificar que haya sesión activa
            if (!isset($_SESSION['usuario_id'])) {
                return response()->json(['error' => 'No active session'], 401);
            }

            // Generar token único
            $token = bin2hex(random_bytes(32));
            
            // Guardar token en sesión
            $_SESSION['api_token'] = $token;
            $_SESSION['token_generated_at'] = now()->timestamp;

            return response()->json([
                'success' => true,
                'token' => $token,
                'expires_in' => config('session.lifetime', 120) * 60
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generating token: ' . $e->getMessage()
            ], 500);
        }
    }
}
