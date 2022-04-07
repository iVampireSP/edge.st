<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'status', 'comment', 'progress', 'order_id', 'user_id', 'end_at'
    ];

    protected $casts = [
        'end_at' => 'datetime'
    ];

    public static function userTasks() {
        return self::where('user_id', user()->id)->limit(50)->latest();
    }
}
