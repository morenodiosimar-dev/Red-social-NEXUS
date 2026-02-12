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

class ChatController extends Controller
{
    /**
     * Mostrar la interfaz principal del chat
     */
    public function index()
    {
        return view('chat.index');
    }

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
            ->where('id', $id)
            ->first();

        if (!$usuario) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return view('chat.historial_personal', [
            'usuario' => $usuario,
            'id_usuario_actual' => $this->getCurrentUserId()
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'sala' => 'required|string',
            'id_usuario' => 'required|integer',
            'nombre_usuario' => 'required|string',
            'mensaje' => 'required|string|max:1000'
        ]);

        try {
            // Guardar mensaje en la base de datos
            $message = DB::table('mensajes')->insert([
                'sala' => $request->sala,
                'usuario' => $request->id_usuario,
                'mensaje' => $request->mensaje,
                'fecha' => now()
            ]);

            // Emitir evento via WebSocket (configurado después)
            broadcast(new \App\Events\NewMessage($request->all()));

            return response()->json(['success' => true, 'message' => 'Mensaje enviado']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al enviar mensaje'], 500);
        }
    }

    public function getContacts()
    {
        $userId = $this->getCurrentUserId();
        
        if (!$userId) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Buscar salas que contengan el ID del usuario
        $salas = DB::table('mensajes')
            ->select('sala')
            ->where('sala', 'LIKE', $userId . '-%')
            ->orWhere('sala', 'LIKE', '%-' . $userId)
            ->distinct()
            ->get();

        $contactosIds = [];
        foreach ($salas as $sala) {
            $partes = explode('-', $sala->sala);
            foreach ($partes as $id) {
                $id = (int)$id;
                if ($id !== $userId && !in_array($id, $contactosIds)) {
                    $contactosIds[] = $id;
                }
            }
        }

        if (empty($contactosIds)) {
            return response()->json([]);
        }

        // Obtener información de los contactos
        $contactos = DB::table('usuarios')
            ->whereIn('id', $contactosIds)
            ->select('id', 'nombre', 'apellido', 'foto_perfil')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre_completo' => trim($usuario->nombre . ' ' . $usuario->apellido),
                    'foto_perfil' => $usuario->foto_perfil ?: 'default.png'
                ];
            });

        return response()->json($contactos);
    }

    public function getMessages($sala)
    {
        $messages = DB::table('mensajes')
            ->select('usuario as id_usuario', 'mensaje', 'fecha')
            ->where('sala', $sala)
            ->orderBy('fecha', 'ASC')
            ->get();

        return response()->json($messages);
    }

    private function getCurrentUserId()
    {
        // Intentar obtener desde sesión (compatible con sistema PHP existente)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['id_usuario'] ?? null;
    }
}
