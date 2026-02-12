<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnline implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $isOnline;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $isOnline = true)
    {
        $this->user = $user;
        $this->isOnline = $isOnline;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return [
            new Channel('online-users'),
            new PrivateChannel('user-status.' . $this->user->id)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'user.status';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'nombre_completo' => $this->user->nombre_completo,
            'foto_perfil' => $this->user->foto_perfil ?: 'default.png',
            'is_online' => $this->isOnline,
            'timestamp' => now()->toISOString()
        ];
    }
}
