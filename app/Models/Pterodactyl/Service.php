<?php

namespace App\Models\Pterodactyl;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'pterodactyl_services';
    
    public $fillable = [
        'package_id', 'docker_image', 'server_id',
        'user_id', 'node_id', 'order_id'
    ];
}
