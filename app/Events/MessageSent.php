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

    public $message;
    public $senderUser;
    public $receiverUser;

    public function __construct($message, $content, User $senderUser, User $receiverUser)
    {
        $this->message = $content;
        $this->senderUser = $senderUser;
        $this->receiverUser = $receiverUser;

        $this->markMessageAsRead($message);
    }

    public function markMessageAsRead($message)
    {
        if ($message->receiver_id == $this->receiverUser->id && $message->read == 0) {
            $message->read = 1;
            $message->save();
        }
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
        return [
            'content' => $this->message,
            'sender_id' => $this->senderUser->id,
            'created_at' => now()->toISOString(), // Convertendo para ISO string
        ];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
