<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $message;
    public $senderUser;
    public $receiverUser;
    public $atualizar = false;

    public function __construct($id, $content, User $senderUser, User $receiverUser)
    {
        $this->id = $id;
        $this->message = $content;
        $this->senderUser = $senderUser;
        $this->receiverUser = $receiverUser;
    }

    public function broadcastOn()
    {
        if (!$this->senderUser || !$this->receiverUser) {
            \Log::error('Usuário remetente ou receptor não está definido');
            return []; // ou outra lógica de fallback
        }
        
        $senderId = $this->senderUser->id; // ID do usuário que está enviando
        $receiverId = $this->receiverUser->id; // Use o ID do receptor
        
        // Garante que o menor ID sempre vem primeiro
        $chatChannelId = $senderId < $receiverId 
        ? $senderId . '.' . $receiverId 
        : $receiverId . '.' . $senderId;
        
        // Retorna o canal concatenado
        return new Channel('chat.' . $chatChannelId);
    }

    public function broadcastWith()
    {
        if($this->message == 'updatePage') {
            $this->atualizar = true;
        }

        return [
            'id' => $this->id,
            'content' => $this->message,
            'sender_id' => $this->senderUser->id,
            'created_at' => now()->toISOString(), // Convertendo para ISO string
            'atualizar' => $this->atualizar,
        ];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
