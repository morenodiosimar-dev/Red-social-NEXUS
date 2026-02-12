<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * La tabla asociada al modelo.
     * Importante: Usamos la tabla existente 'usuarios' del sistema principal
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo debe ser timestamped.
     * Desactivado para compatibilidad con tabla existente
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido', 
        'correo',
        'telefono',
        'fechaN',
        'sexo',
        'foto_perfil',
        'contraseña'
    ];

    /**
     * Los atributos que deben ocultarse para los arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fechaN' => 'date',
        'contraseña' => 'hashed',
    ];

    /**
     * Obtener el nombre completo del usuario
     *
     * @return string
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . ($this->apellido ?? ''));
    }

    /**
     * Obtener la URL del foto de perfil
     *
     * @return string
     */
    public function getFotoPerfilUrlAttribute()
    {
        if ($this->foto_perfil && $this->foto_perfil !== 'default.png') {
            return '../uploads/' . $this->foto_perfil;
        }
        
        return '../assets/default-avatar.png';
    }

    /**
     * Relación con los mensajes enviados
     */
    public function mensajes(): HasMany
    {
        return $this->hasMany(Message::class, 'usuario');
    }

    /**
     * Obtener los contactos del usuario (basado en mensajes compartidos)
     */
    public function getContactsAttribute()
    {
        // Buscar salas que contengan el ID del usuario
        $salas = \DB::table('mensajes')
            ->select('sala')
            ->where('sala', 'LIKE', $this->id . '-%')
            ->orWhere('sala', 'LIKE', '%-' . $this->id)
            ->distinct()
            ->pluck('sala');

        // Extraer IDs de contactos únicos
        $contactosIds = [];
        foreach ($salas as $sala) {
            $partes = explode('-', $sala);
            foreach ($partes as $id) {
                $id = (int)$id;
                if ($id !== $this->id && !in_array($id, $contactosIds)) {
                    $contactosIds[] = $id;
                }
            }
        }

        // Obtener información de los contactos
        return self::whereIn('id', $contactosIds)
            ->select('id', 'nombre', 'apellido', 'foto_perfil')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'foto_perfil' => $usuario->foto_perfil ?: 'default.png'
                ];
            });
    }

    /**
     * Verificar si el usuario está en línea (simulado - podría usar cache)
     */
    public function getIsOnlineAttribute()
    {
        // Implementación básica - en producción usar Redis o similar
        return cache()->has('user-online-' . $this->id);
    }

    /**
     * Marcar usuario como en línea
     */
    public function markAsOnline()
    {
        cache()->put('user-online-' . $this->id, true, now()->addMinutes(5));
    }

    /**
     * Marcar usuario como desconectado
     */
    public function markAsOffline()
    {
        cache()->forget('user-online-' . $this->id);
    }
}
