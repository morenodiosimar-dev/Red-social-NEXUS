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
    /**
     * Obtener datos del usuario actual
     * Soporta sesión local (PHP nativo) y Autenticación Remota por Firma (HMAC)
     */
    public function getCurrentUser(Request $request)
    {
        try {
            $userId = null;
            $source = 'guest';

            // 1. Intentar Autenticación Remota (Prioridad para integración)
            if ($request->filled(['user_id', 'signature'])) {
                $remoteId = $request->input('user_id');
                $timestamp = $request->input('timestamp', 0); // Opcional: para evitar replay attacks
                $signature = $request->input('signature');

                if ($this->verifySignature($remoteId, $signature, $timestamp)) {
                    $userId = $remoteId;
                    $source = 'remote_signature';
                }
            }

            // 2. Si no hay remoto, intentar Sesión PHP Nativa (Legacy/Local)
            if (!$userId) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['usuario_id'])) {
                    $userId = $_SESSION['usuario_id'];
                    $source = 'native_session';
                }
            }

            // 3. Procesar Usuario Encontrado
            if ($userId) {
                $usuario = User::find($userId);

                if ($usuario) {
                    // Login manual en Laravel para esta petición
                    Auth::login($usuario);

                    // Marcar leídos y online
                    $usuario->markAsOnline();

                    // Solo emitir evento si es una conexión real de socket (opcional)
                    // broadcast(new UserOnline($usuario, true));

                    return response()->json([
                        'success' => true,
                        'id_usuario' => $usuario->id,
                        'usuario' => $usuario->nombre_completo,
                        'foto_perfil' => $usuario->foto_perfil ?: 'default.png',
                        'auth_source' => $source
                    ]);
                }
            }

            // 4. Usuario no encontrado / Invitado
            return response()->json([
                'success' => false,
                'id_usuario' => null,
                'usuario' => 'Invitado-' . rand(1000, 9999),
                'error' => 'No session or valid signature found'
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Chat Auth Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica la firma HMAC enviada por Proyecto_Prueba
     */
    private function verifySignature($userId, $signature, $timestamp = 0)
    {
        // Secreto compartido - DEBE coincidir con el de Proyecto_Prueba
        // Por defecto usamos APP_KEY para simplificar, pero idealmente sería una variable dedicada
        $secret = config('app.key');

        // El payload esperado debe coincidir con el generado en el otro lado
        // Formato simple: user_id
        // Formato seguro: user_id.timestamp
        $payload = $timestamp ? "{$userId}.{$timestamp}" : (string) $userId;

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
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
                $id = (int) $id;
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
