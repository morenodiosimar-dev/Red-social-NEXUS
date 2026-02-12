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
    public $id_usuario;
    public $nombre_usuario;
    public $mensaje;

    public function __construct(array $data)
    {
        $this->sala = $data['sala'];
        $this->id_usuario = $data['id_usuario'];
        $this->nombre_usuario = $data['nombre_usuario'];
        $this->mensaje = $data['mensaje'];
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->sala);
    }

    public function broadcastAs()
    {
        return 'new.message';
    }

    public function broadcastWith()
    {
        return [
            'sala' => $this->sala,
            'id_usuario' => $this->id_usuario,
            'nombre_usuario' => $this->nombre_usuario,
            'mensaje' => $this->mensaje,
            'timestamp' => now()->toISOString()
        ];
    }
}
