<?php

namespace App\Http\Controllers\Pterodactyl;

use App\Drivers\Server\Panel\Pterodactyl\Access;
use App\Http\Controllers\Controller;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\NestEgg;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    // Refresh Nests and Eggs
    public static function refresh_nests()
    {
        $panel = new Access();
        $nests = (object)$panel->nests();
        if (!$nests) {
            return 0;
        }
        $arr = self::search($nests);

        $ids = [];
        foreach ($arr as $a) {
            $wingsNest = new Nest();
            $attr = $a['attributes'];
            $nest_id = $attr['id'];
            $create_data = [
                'nest_id' => $nest_id,
                'author' => $attr['author'],
                'name' => $attr['name'],
                'description' => $attr['description'],
            ];
            $wingsNest_where = $wingsNest->where('nest_id', $nest_id);
            if ($wingsNest_where->exists()) {
                $ids[] = $nest_id;
                $create_data['found'] = 1;
                $wingsNest_where->update($create_data);
            } else {
                $ids[] = $nest_id;
                $wingsNest->create($create_data);
            }
        }

        Nest::whereNotIn('nest_id', $ids)->update(['found' => 0]);

        return $arr;
    }

    public static function refresh_eggs()
    {
        $panel = new Access();

        Nest::chunk(100, function ($nests) use ($panel) {
            $egg_ids = [];

            foreach ($nests as $nest) {
                // Refresh Eggs
                // Search for eggs in nest
                $eggs = $panel->eggs($nest->nest_id);
                if (!$eggs) {
                    continue;
                }
                foreach ($eggs['data'] as $egg) {
                    $wingsNestEgg = new NestEgg();
                    $egg = $egg['attributes'];

                    $egg_data = [
                        'name' => $egg['name'],
                        'nest_id' => $egg['nest'],
                        'author' => $egg['author'],
                        'description' => $egg['description'],
                        'docker_images' => $egg['docker_images'],
                        'startup' => $egg['startup'],
                        'egg_id' => $egg['id'],
                        'environment' => $egg['relationships']['variables']['data'],
                    ];

                    $wingsNestEgg_where = $wingsNestEgg->where('egg_id', $egg['id']);
                    if ($wingsNestEgg_where->exists()) {
                        $egg_ids[] = $egg['id'];
                        $egg_data['found'] = 1;
                        $wingsNestEgg_where->update($egg_data);
                    } else {
                        $egg_ids[] = $egg['id'];
                        $wingsNestEgg->create($egg_data);
                    }
                }
            }

            // Update Egg found column
            NestEgg::whereNotIn('egg_id', $egg_ids)->update(['found' => 0]);
        });

        return true;
    }

    public static function updateCountColumn()
    {
        //  Refresh nests counts
        Nest::chunk(100, function ($nests) {
            foreach ($nests as $nest) {
                $nest->eggs = NestEgg::where('nest_id', $nest->id)->count();
                $nest->save();
            }
        });
    }

    public static function search($data)
    {
        $data = (object)$data;
        $next_page = 1;
        $is_continue = true;
        $arr = [];
        do {
            $total_page = $data->meta['pagination']['total_pages'];
            if ($next_page == $total_page) {
                $is_continue = false;
            } else {
                $next_page = $data->meta['pagination']['current_page'] + 1;
            }

            foreach ($data->data as $d) {
                array_push($arr, $d);
            }
        } while ($is_continue);

        return $arr;
    }
}
