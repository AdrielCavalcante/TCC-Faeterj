<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Message extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'file_path',
        'encrypted',
        'encryption_key_sender',
        'encryption_key_receiver',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getAuditType()
    {
        return 'message_audit'; // Tipo de auditoria específico para mensagens
    }
}
