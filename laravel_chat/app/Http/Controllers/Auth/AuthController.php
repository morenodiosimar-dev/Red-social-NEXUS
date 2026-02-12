<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function getUserData(Request $request)
    {
        // Iniciar sesión si no está activa (compatible con sistema existente)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está autenticado en el sistema PHP existente
        if (isset($_SESSION['id_usuario']) && isset($_SESSION['usuario'])) {
            return response()->json([
                'id_usuario' => $_SESSION['id_usuario'],
                'usuario' => $_SESSION['usuario']
            ]);
        }

        // Si no hay sesión, intentar con cookies del sistema existente
        $token = $request->cookie('auth_token') ?? null;
        
        if ($token) {
            // Lógica de validación de token del sistema existente
            $user = DB::table('usuarios')
                ->where('remember_token', $token)
                ->first();

            if ($user) {
                $_SESSION['id_usuario'] = $user->id;
                $_SESSION['usuario'] = $user->nombre;
                
                return response()->json([
                    'id_usuario' => $user->id,
                    'usuario' => $user->nombre
                ]);
            }
        }

        // Usuario no autenticado
        return response()->json([
            'id_usuario' => null,
            'usuario' => 'Invitado-' . rand(1000, 9999)
        ]);
    }

    public function getUsers(Request $request)
    {
        $search = $request->get('q', '');
        
        $query = DB::table('usuarios')
            ->select('id', 'nombre', 'apellido', 'foto_perfil')
            ->where('id', '>', 0)
            ->orderBy('nombre', 'ASC');

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', '%' . $search . '%')
                  ->orWhere('apellido', 'LIKE', '%' . $search . '%');
            });
        }

        $users = $query->limit(20)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'nombre' => trim($user->nombre . ' ' . $user->apellido),
                'foto_perfil' => $user->foto_perfil ?: 'default.png'
            ];
        });

        return response()->json($users);
    }
}
