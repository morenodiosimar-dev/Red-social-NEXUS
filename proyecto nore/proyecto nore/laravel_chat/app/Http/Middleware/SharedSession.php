<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class SharedSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Iniciar sesión PHP si no está activa (compatibilidad con sistema principal)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si existe una sesión del sistema principal
        if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre'])) {
            // Obtener usuario del modelo Laravel
            $user = User::find($_SESSION['usuario_id']);
            
            if ($user) {
                // Establecer usuario actual en Laravel
                auth()->login($user);
                
                // Actualizar datos de sesión si es necesario
                if ($_SESSION['nombre'] !== $user->nombre || 
                    ($_SESSION['apellido'] ?? '') !== ($user->apellido ?? '')) {
                    $_SESSION['nombre'] = $user->nombre;
                    $_SESSION['apellido'] = $user->apellido;
                }
                
                // Marcar hora de última actividad
                $_SESSION['ultima_actividad'] = now()->timestamp;
            }
        }

        // Protección contra sesión expirada
        if (isset($_SESSION['ultima_actividad'])) {
            $inactividad = now()->timestamp - $_SESSION['ultima_actividad'];
            $tiempoMaximoInactividad = config('session.lifetime', 120) * 60; // minutos a segundos
            
            if ($inactividad > $tiempoMaximoInactividad) {
                // Destruir sesión expirada
                session_destroy();
                auth()->logout();
                
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Sesión expirada'], 401);
                } else {
                    return redirect()->away('../login.php?error=session_expired');
                }
            }
        }

        // Actualizar timestamp de actividad
        if (isset($_SESSION['usuario_id'])) {
            $_SESSION['ultima_actividad'] = now()->timestamp;
        }

        // Continuar con la solicitud
        $response = $next($request);

        // Asegurar que las cabeceras de sesión se envíen correctamente
        if (!headers_sent()) {
            // Configurar SameSite para compatibilidad con iframes si es necesario
            $sameSite = config('session.same_site', 'lax');
            $secure = config('session.secure', true);
            
            session_set_cookie_params([
                'lifetime' => config('session.lifetime', 120) * 60,
                'path' => config('session.path', '/'),
                'domain' => config('session.domain', ''),
                'secure' => $secure,
                'httponly' => config('session.http_only', true),
                'samesite' => $sameSite
            ]);
        }

        return $response;
    }
}
