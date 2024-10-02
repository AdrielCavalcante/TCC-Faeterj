<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'private_key'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
