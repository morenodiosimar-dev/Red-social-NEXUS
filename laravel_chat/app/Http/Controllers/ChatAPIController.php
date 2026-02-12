<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Events\NewMessage;
use App\Events\TypingEvent;
use App\Events\UserOnline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ChatAPIController extends Controller
{
    /**
     * Obtener datos del usuario actual (compatibilidad con sistema PHP)
     */
    public function getCurrentUser()
    {
        try {
            // Iniciar sesión si no está activa (compatibilidad con sistema existente)
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Verificar si el usuario está autenticado en el sistema PHP existente
            if (isset($_SESSION['usuario_id']) && isset($_SESSION['nombre'])) {
                $usuario = User::find($_SESSION['usuario_id']);
                
                if ($usuario) {
                    // Marcar usuario como en línea
                    $usuario->markAsOnline();
                    broadcast(new UserOnline($usuario, true));

                    return response()->json([
                        'success' => true,
                        'id_usuario' => $usuario->id,
                        'usuario' => $usuario->nombre_completo,
                        'foto_perfil' => $usuario->foto_perfil ?: 'default.png'
                    ]);
                }
            }

            // Usuario no autenticado
            return response()->json([
                'success' => false,
                'id_usuario' => null,
                'usuario' => 'Invitado-' . rand(1000, 9999)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error obteniendo datos del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener lista de usuarios disponibles para chatear
     */
    public function getUsers(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $currentUserId = $this->getCurrentUserId();
            
            if (!$currentUserId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $query = User::where('id', '!=', $currentUserId)
                ->select('id', 'nombre', 'apellido', 'foto_perfil');

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'LIKE', '%' . $search . '%')
                      ->orWhere('apellido', 'LIKE', '%' . $search . '%');
                });
            }

            $users = $query->limit(20)->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->nombre_completo,
                    'foto_perfil' => $user->foto_perfil ?: 'default.png',
                    'is_online' => $user->is_online
                ];
            });

            return response()->json($users);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error obteniendo usuarios: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener contactos del usuario (basado en mensajes compartidos)
     */
    public function getContacts()
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            
            if (!$currentUserId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            $user = User::find($currentUserId);
            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            return response()->json($user->contacts);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error obteniendo contactos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Enviar un nuevo mensaje
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'sala' => 'required|string|max:50',
                'mensaje' => 'required|string|max:1000'
            ]);

            $currentUserId = $this->getCurrentUserId();
            $currentUserName = $this->getCurrentUserName();

            if (!$currentUserId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Crear y guardar el mensaje
            $message = Message::create([
                'sala' => $request->sala,
                'usuario' => $currentUserId,
                'mensaje' => $request->mensaje,
                'fecha' => now()
            ]);

            // Determinar destinatario
            $partes = explode('-', $request->sala);
            $destinatarioId = collect($partes)->first(fn($id) => (int)$id !== $currentUserId);

            // Emitir evento broadcasting
            broadcast(new NewMessage($message, $destinatarioId));

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => [
                    'id' => $message->id,
                    'sala' => $message->sala,
                    'usuario' => $message->usuario,
                    'nombre_usuario' => $currentUserName,
                    'mensaje' => $message->mensaje,
                    'fecha' => $message->fecha->toISOString(),
                    'tiempo_formateado' => $message->tiempo_formateado
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error enviando mensaje: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener historial de mensajes de una sala
     */
    public function getMessages($sala)
    {
        try {
            $currentUserId = $this->getCurrentUserId();
            
            if (!$currentUserId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Verificar que el usuario pertenezca a la sala
            $partes = explode('-', $sala);
            if (!in_array($currentUserId, $partes)) {
                return response()->json(['error' => 'No tienes acceso a esta sala'], 403);
            }

            $messages = Message::deSala($sala)
                ->ordenados()
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'id_usuario' => $message->usuario,
                        'nombre_usuario' => $message->nombre_remitente,
                        'mensaje' => $message->mensaje,
                        'fecha' => $message->fecha->toISOString(),
                        'tiempo_formateado' => $message->tiempo_formateado,
                        'foto_remitente' => $message->foto_remitente,
                        'propio' => $message->usuario == $this->getCurrentUserId()
                    ];
                });

            return response()->json($messages);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error obteniendo mensajes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Indicar que un usuario está escribiendo
     */
    public function sendTyping(Request $request)
    {
        try {
            $request->validate([
                'sala' => 'required|string',
                'is_typing' => 'required|boolean'
            ]);

            $currentUserId = $this->getCurrentUserId();
            $currentUserName = $this->getCurrentUserName();

            if (!$currentUserId) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // Emitir evento de typing
            broadcast(new TypingEvent(
                $request->sala,
                $currentUserId,
                $currentUserName,
                $request->is_typing
            ));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error con typing: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener usuarios en línea
     */
    public function getOnlineUsers()
    {
        try {
            $onlineUsers = Cache::remember('online-users', 60, function () {
                return User::whereHas('mensajes')
                    ->select('id', 'nombre', 'apellido', 'foto_perfil')
                    ->get()
                    ->filter(function ($user) {
                        return $user->is_online;
                    })
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'nombre_completo' => $user->nombre_completo,
                            'foto_perfil' => $user->foto_perfil ?: 'default.png'
                        ];
                    })
                    ->keyBy('id')
                    ->toArray();
            });

            return response()->json($onlineUsers);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error obteniendo usuarios online: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Obtener ID del usuario actual
     */
    private function getCurrentUserId()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Helper: Obtener nombre del usuario actual
     */
    private function getCurrentUserName()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
    }
}
