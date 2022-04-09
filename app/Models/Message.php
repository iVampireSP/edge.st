<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['channel', 'message', 'type'];

    protected $casts = [
        'message' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
