<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sala;
    public $from_id;
    public $to_id;
    public $body;
    public $id_usuario;
    public $nombre_usuario;
    public $mensaje;

    public function __construct(array $data)
    {
        $this->sala = $data['sala'];
        $this->from_id = $data['from_id'] ?? ($data['id_usuario'] ?? null);
        $this->to_id = $data['to_id'] ?? null;
        $this->body = $data['body'] ?? ($data['mensaje'] ?? '');
        
        // Mantener compatibilidad con campos antiguos
        $this->id_usuario = $data['id_usuario'] ?? $this->from_id;
        $this->nombre_usuario = $data['nombre_usuario'] ?? 'Usuario';
        $this->mensaje = $data['mensaje'] ?? $this->body;
    }

    public function broadcastOn()
    {
        // Emitimos en múltiples canales para máxima compatibilidad
        return [
            new Channel('chat.' . $this->sala),
            new Channel('chat.' . $this->from_id),
            new Channel('chat.' . $this->to_id)
        ];
    }

    public function broadcastAs()
    {
        return 'NewMessage';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'sala' => $this->sala,
                'from_id' => $this->from_id,
                'to_id' => $this->to_id,
                'body' => $this->body,
                'id_usuario' => $this->id_usuario,
                'nombre_usuario' => $this->nombre_usuario,
                'mensaje' => $this->mensaje,
                'timestamp' => now()->toISOString()
            ]
        ];
    }
}
