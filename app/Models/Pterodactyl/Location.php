<?php

namespace App\Models\Pterodactyl;

use App\Drivers\Server\Panel\Pterodactyl\Access;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'pterodactyl_locations';

    protected $fillable = ['name', 'short', 'location_id', 'status', 'visibility', 'node_count'];

    public static function cacheLocations($page = 1)
    {
        $access = new Access();
        $locations = $access->locations($page);

        foreach ($locations['data'] as $location) {
            $data = $location['attributes'];

            $this_location = self::where('location_id', $data['id'])->first();

            if (is_null($this_location)) {
                self::create([
                    'name' => $data['short'],
                    'short' => $data['short'],
                    'location_id' => $data['id'],
                ]);
            } else {
                self::where('location_id', $data['id'])->update([
                    'name' => $data['short'],
                    'short' => $data['short'],
                ]);
            }
        }
    }
}
