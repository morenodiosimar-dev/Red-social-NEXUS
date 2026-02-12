<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sala;
    public $usuario;
    public $nombreUsuario;
    public $isTyping;

    /**
     * Create a new event instance.
     */
    public function __construct($sala, $usuario, $nombreUsuario, $isTyping = true)
    {
        $this->sala = $sala;
        $this->usuario = $usuario;
        $this->nombreUsuario = $nombreUsuario;
        $this->isTyping = $isTyping;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat.' . $this->sala),
            new Channel('typing-indicators')
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'TypingEvent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'sala' => $this->sala,
            'usuario' => $this->usuario,
            'user' => [
                'id' => $this->usuario,
                'nombre' => $this->nombreUsuario
            ],
            'is_typing' => $this->isTyping,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Determine if this event should be broadcast.
     */
    public function broadcastWhen()
    {
        return true; // Siempre broadcastear indicadores de escritura
    }
}
