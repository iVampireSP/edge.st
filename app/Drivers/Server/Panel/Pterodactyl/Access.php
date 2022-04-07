<?php

namespace App\Drivers\Server\Panel\Pterodactyl;

use ErrorException;
use App\Drivers\Driver;
use Illuminate\Support\Str;

class Access extends Driver
{
    private $api;

    public function __construct()
    {
        $this->api = new API();
    }

    public function create(
        string $server_name,
        int $panel_user_id,
        int $egg_id,
        string $docker_image,
        string $startup,
        array $environment,
        int $memory,
        int $swap,
        int $disk,
        int $io,
        int $cpu,
        int $databases,
        int $backups,
        int $allocation_id
    ) {
        $data = [
            'name' => $server_name,
            'user' => $panel_user_id,
            'egg' => $egg_id,
            'docker_image' => $docker_image,
            'startup' => $startup,
            'environment' => $environment,
            'limits' => [
                'memory' => $memory,
                'swap' => $swap,
                'disk' => $disk,
                'io' => $io,
                'cpu' => $cpu,
            ],
            'feature_limits' => [
                'databases' => $databases,
                'backups' => $backups,
            ],
            "allocation" => [
                "default" => $allocation_id
            ]
        ];

        return $this->api->createServer($data);
    }

    public function generateCreateArray(
        string $server_name,
        int $panel_user_id,
        int $egg_id,
        string $docker_image,
        string $startup,
        array $environment,
        int $memory,
        int $swap,
        int $disk,
        int $io,
        int $cpu,
        int $databases,
        int $backups,
        int $allocation_id
    ) {
        $data = [
            'name' => $server_name,
            'user' => $panel_user_id,
            'egg' => $egg_id,
            'docker_image' => $docker_image,
            'startup' => $startup,
            'environment' => $environment,
            'limits' => [
                'memory' => $memory,
                'swap' => $swap,
                'disk' => $disk,
                'io' => $io,
                'cpu' => $cpu,
            ],
            'feature_limits' => [
                'databases' => $databases,
                'backups' => $backups,
            ],
            "allocation" => [
                "default" => $allocation_id
            ]
        ];

        return $data;
    }

    public function createArray($data)
    {
        return $this->api->createServer($data);
    }

    public function delete($server_id)
    {
        return $this->api->delete('/servers/' . $server_id);
    }

    public function suspend($server_id)
    {
        return $this->api->suspendServer('/servers/' . $server_id);
    }

    public function unsuspend($server_id)
    {
        return $this->api->unsuspendServer('/servers/' . $server_id);
    }

    public function update()
    {
    }

    // public function locations()
    // {
    //     return $this->api->
    // }

    public function user($email)
    {
        return $this->api->userFind($email)['attributes'];
    }

    public function createUser($email, $username, $first_name = false, $last_name = false, $password = false)
    {
        if (!$password) {
            $password = Str::random(16);
        }

        // fake first name
        $faker = \Faker\Factory::create();

        if (!$first_name) {
            $first_name = $faker->firstName;
        }

        if (!$last_name) {
            $last_name = $faker->last_name;
        }

        return $this->api->userCreate([
            'email' => $email,
            'password' => $password,
            'username' => $username,
            'first_name' => $first_name,
            'last_name' => $last_name,
        ])['attributes'];
    }


    public function users()
    {
        return $this->api->users();
    }

    public function locations()
    {
        return $this->api->locations();
    }

    public function nodes()
    {
        return $this->api->nodes();
    }

    public function nests()
    {
        return $this->api->nests();
    }

    public function eggs($nest_id)
    {
        return $this->api->eggs($nest_id);
    }

    public function nextAllocationId($node_id)
    {
        $next_page = 1;
        $is_continue = true;
        $selected_id = 0;
        do {
            $allocations_data = $this->api->allocations($node_id, $next_page);
            $total_page = $allocations_data['meta']['pagination']['total_pages'];

            if ($next_page == $total_page) {
                $is_continue = false;
            } else {
                $next_page = $allocations_data['meta']['pagination']['current_page'] + 1;
            }

            foreach ($allocations_data['data'] as $allocation) {
                if (!$allocation['attributes']['assigned']) {
                    $selected_id = $allocation['attributes']['id'];
                    return $selected_id;
                }
            }
        } while ($is_continue);
    }
}
