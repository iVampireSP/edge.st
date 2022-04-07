<?php

namespace App\Models\Pterodactyl;

use Illuminate\Database\Eloquent\Model;

class NestEgg extends Model
{
    protected $table = 'pterodactyl_nest_eggs';

    protected $fillable = [
        'name', 'nest_id', 'author', 'description', 'docker_images', 'startup', 'egg_id', 'environment'
    ];

    protected $casts = [
        'docker_images' => 'array',
        'environment' => 'json'
    ];
}
