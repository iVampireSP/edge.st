<?php

namespace App\Drivers\Server\Panel\Pterodactyl;

use App\Drivers\Driver;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Client\RequestException;

class API extends Driver
{
    protected $url, $http;

    public function __construct()
    {
        $this->url = config('pterodactyl.url') . '/api/application';
        $this->http = Http::withToken(config('pterodactyl.key'));
    }

    // Users
    public function users()
    {
        return $this->get('/users');
    }

    // user
    public function user($id)
    {
        return $this->get('/users/' . $id);
    }

    public function userCreate($data)
    {
        return $this->post('/users', $data);
    }

    public function userFind($email)
    {
        $user = $this->get('/users?filter[email]=' . urlencode($email));
        if (count($user['data']) === 0) {
            throw new Exception('User not found.');
        } else {
            return $user['data'][0];
        }
    }

    public function deleteUser($id)
    {
        return $this->delete('/users/' . $id);
    }

    public function updateUser($id, $data)
    {
        return $this->patch('/users/' . $id, $data);
    }

    public function getRandomNode($location_id)
    {
        $nodes = $this->get('/locations/' . $location_id . '?include=nodes');
        if (!$nodes) {
            return false;
        }
        $nodes = $nodes['attributes']['relationships']['nodes']['data'];
        return $nodes[rand(0, count($nodes) - 1)]['attributes'];
    }


    public function createServer($data)
    {
        return $this->post('/servers', $data);
    }

    public function deleteServer($id)
    {
        return $this->delete('/servers/' . $id);
    }

    public function suspendServer($id)
    {
        return $this->post('/servers/' . $id . '/suspend');
    }

    public function unsuspendServer($id)
    {
        return $this->post('/servers/' . $id . '/unsuspend');
    }

    public function allocations($node_id, $page = 1)
    {
        return $this->get('/nodes/' . $node_id . '/allocations?include=server&page=' . $page);
    }

    public function locations($page = 1)
    {
        return $this->get('/locations' . '?page=' . $page);
    }

    public function nodes($page = 1)
    {
        return $this->get('/nodes' . '?page=' . $page);
    }

    // Nests
    public function nests($page = 0)
    {
        return $this->get('/nests' . '?page=' . $page);
    }

    public function nest($id)
    {
        return $this->get('/nests/' . $id);
    }

    public function eggs($id)
    {
        return $this->get('/nests/' . $id . '/eggs?include=variables');
    }

    public function egg($nest_id, $egg_id)
    {
        return $this->get('/nests/' . $nest_id . '/eggs/' . $egg_id);
    }

    public function eggVar($nest_id, $egg_id)
    {
        return $this->get('/nests/' . $nest_id . '/eggs/' . $egg_id . '?include=variables');
    }

    public function get($url, $data = null)
    {
        $response = $this->http->get($this->url . $url, $data);
        $response->throw();

        if ($response->failed()) {
            return false;
        } else {
            return $response->json() ?? false;
        }
    }

    public function post($url, $data = null)
    {
        $response = $this->http->post($this->url . $url, $data);
        $response->throw();
        if ($response->failed()) {
            return false;
        } else {
            return $response->json();
        }
    }

    public function patch($url, $data = null)
    {
        $response = $this->http->patch($this->url . $url, $data);
        $response->throw();
        if ($response->failed()) {
            return false;
        } else {
            return $response->json();
        }
    }

    public function delete($url, $data = null)
    {
        $response = $this->http->delete($this->url . $url, $data);
        $response->throw();
        if ($response->failed()) {
            return false;
        } else {
            return true;
        }
    }
}
