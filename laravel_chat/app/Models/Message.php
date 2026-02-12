<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'mensajes';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indica si el modelo usa timestamps.
     * Usamos el campo 'fecha' existente en lugar de created_at/updated_at
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
        'sala',
        'usuario',
        'mensaje',
        'fecha',
        'leido'
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'datetime',
        'leido' => 'boolean',
    ];

    /**
     * Los atributos que deben tener valores por defecto.
     *
     * @var array
     */
    protected $attributes = [
        'leido' => false,
    ];

    /**
     * Relación con el usuario que envió el mensaje
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario');
    }

    /**
     * Obtener el nombre completo del remitente
     *
     * @return string
     */
    public function getNombreRemitenteAttribute()
    {
        return $this->usuario ? $this->usuario->nombre_completo : 'Usuario Desconocido';
    }

    /**
     * Obtener la foto de perfil del remitente
     *
     * @return string
     */
    public function getFotoRemitenteAttribute()
    {
        return $this->usuario ? ($this->usuario->foto_perfil ?: 'default.png') : 'default.png';
    }

    /**
     * Obtener la sala formateada para mostrar
     *
     * @return string
     */
    public function getSalaFormateadaAttribute()
    {
        $partes = explode('-', $this->sala);
        if (count($partes) === 2) {
            return "Chat entre Usuario {$partes[0]} y Usuario {$partes[1]}";
        }
        return $this->sala;
    }

    /**
     * Scope para obtener mensajes de una sala específica
     */
    public function scopeDeSala($query, $sala)
    {
        return $query->where('sala', $sala);
    }

    /**
     * Scope para obtener mensajes de un usuario específico
     */
    public function scopeDeUsuario($query, $usuarioId)
    {
        return $query->where('usuario', $usuarioId);
    }

    /**
     * Scope para obtener mensajes no leídos
     */
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    /**
     * Scope para obtener mensajes ordenados por fecha
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('fecha', 'asc');
    }

    /**
     * Marcar mensaje como leído
     */
    public function marcarComoLeido()
    {
        $this->leido = true;
        $this->save();
    }

    /**
     * Obtener el tiempo formateado del mensaje
     *
     * @return string
     */
    public function getTiempoFormateadoAttribute()
    {
        if (!$this->fecha) {
            return '';
        }

        $ahora = now();
        $diferencia = $ahora->diffInMinutes($this->fecha);

        if ($diferencia < 1) {
            return 'Ahora';
        } elseif ($diferencia < 60) {
            return "Hace {$diferencia} min";
        } elseif ($diferencia < 1440) { // 24 horas
            $horas = floor($diferencia / 60);
            return "Hace {$horas}h";
        } elseif ($diferencia < 2880) { // 48 horas
            return 'Ayer';
        } else {
            return $this->fecha->format('d/m/Y');
        }
    }

    /**
     * Generar ID único de sala entre dos usuarios
     */
    public static function generarSala($usuario1Id, $usuario2Id)
    {
        return $usuario1Id < $usuario2Id 
            ? "{$usuario1Id}-{$usuario2Id}" 
            : "{$usuario2Id}-{$usuario1Id}";
    }

    /**
     * Obtener el otro participante de la sala
     */
    public function getOtroParticipanteAttribute($usuarioActualId)
    {
        $partes = explode('-', $this->sala);
        $otroId = collect($partes)->first(fn($id) => (int)$id !== (int)$usuarioActualId);
        
        return User::find($otroId);
    }
}
