<?php

namespace App\Models\Pterodactyl;

use Illuminate\Database\Eloquent\Model;

class Nest extends Model
{
    protected $table = 'pterodactyl_nests';

    protected $fillable = [
        'nest_id', 'author', 'name', 'description'
    ];
}
