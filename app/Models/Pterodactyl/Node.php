<?php

namespace App\Models\Pterodactyl;

use Illuminate\Database\Eloquent\Model;
use App\Drivers\Server\Panel\Pterodactyl\Access;

class Node extends Model
{
    protected $table = 'pterodactyl_nodes';

    protected $fillable = [
        'name', 'display_name', 'maintenance_mode', 'visibility', 'memory', 'disk', 'location_id', 'node_id', 'server_count'
    ];

    // sync node from pterodactyl Panel
    public static function syncNode($page = 1)
    {
        if (!Location::count()) {
            Location::cacheLocations();
        }

        $access = new Access();
        $nodes = $access->nodes($page);

        foreach ($nodes['data'] as $node) {
            $data = $node['attributes'];

            $this_node = self::where('node_id', $data['id'])->first();

            // find location using location_id
            $location = Location::where('location_id', $data['location_id'])->first();

            if (is_null($this_node)) {
                self::create([
                    'name' => $data['name'],
                    'display_name' => $data['name'],
                    'memory' => $data['memory'],
                    'disk' => $data['disk'],
                    'node_id' => $data['id'],
                    'location_id' => $location->id,
                ]);
            } else {
                self::where('node_id', $data['id'])->update([
                    'name' => $data['name'],
                    'memory' => $data['memory'],
                    'disk' => $data['disk'],
                    'location_id' => $location->id,
                ]);
            }
        }
    }
}
