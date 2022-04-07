<?php

namespace App\Http\Controllers\Pterodactyl;

use Exception;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\NestEgg;
use App\Models\Pterodactyl\Package;
use App\Http\Controllers\Controller;
use App\Models\Pterodactyl\Location;
use App\Drivers\Server\Panel\Pterodactyl\Access;
use App\Jobs\AsyncJob;
use App\Models\Pterodactyl\Node;
use App\Models\Pterodactyl\Service;

class ServerController extends Controller
{

    public $access;

    public $request;
    public $product;
    public $order;

    public $create_validate = [
        'name' => 'required|string|max:10|alpha_dash',
        'egg_id' => 'required',
        'docker_image' => 'required',
        'package_id' => 'required',
        'node_id' => 'required'
    ];

    public function __construct()
    {
        $this->access = new Access();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // test func and return
        // $users = $this->access->create();
        // return success($users);
    }

    public function create()
    {
        // 返回需要的字段

        $response_data = [
            'nests' => [
                'nest',
                Nest::select(['name'])->get()
            ],
            'nest_eggs' => [
                'nest_eggs',
                NestEgg::select(['name', 'description', 'docker_images'])->get()
            ],
            'packages' => [
                'package_id',
                Package::get()
            ],
            'locations' => [
                'location_id',
                Location::get()
            ],
            'nodes' => [
                'node_id',
                Node::get()
            ],

            'validate' => $this->create_validate
        ];


        return success($response_data);
    }

    public function store(Request $request)
    {
        // 当创建一个服务器时，应该先创建一个订单
        // 订单包含 月付费，优惠码，每分钟应付
        // 

        //  array_merge(['id' => 'required'], $p->toArray()['validate'])
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function setup()
    {

        dispatch(function() {
            echo 1;
        });

        return false;
        // dispatch(new AsyncJob(function() {
        //     echo 1;
        // }));
        // 先验证参数
        $this->validate($this->request, $this->create_validate);

        // 验证镜像是否可用
        $egg = NestEgg::findOrFail($this->request->egg_id);

        // dd(1);
        // {
        //     "CHEATS": "false",
        //     "GAMEMODE": "survival",
        //     "DIFFICULTY": "hard",
        //     "SERVERNAME": "Amber.edge.st",
        //     "BEDROCK_VERSION": "latest",
        //     "LD_LIBRARY_PATH": "."
        // }

        // 验证docker_image是否存在
        if (!in_array($this->request->docker_image, $egg->docker_images)) {
            throw new Exception('Docker image not found.');
        }

        // 验证 Node 是否存在
        $node = Node::findOrFail($this->request->node_id);

        // 验证 Package 是否存在
        $package = Package::findOrFail($this->request->package_id);

        $access = new Access();

        try {
            $panel_user = $access->user(user()->email);
        } catch (Exception) {
            // 创建用户
            $panel_user = $access->createUser(user()->email, user()->name);
        }

        $allocation = $access->nextAllocationId($node->node_id);

        // 处理 environment
        $environment = [];
        foreach ($egg->environment as $env) {
            $env = $env['attributes'];
            $environment[$env['env_variable']] = $env['default_value'];
        }

        // 执行创建服务器
        // dispatch to async job

        dispatch(new AsyncJob(function () use ($access, $panel_user, $egg, $environment, $package, $allocation, $node) {
            // $create = $access->create(
            //     $this->request->name,
            //     $panel_user['id'],
            //     $egg->egg_id,
            //     $this->request->docker_image,
            //     $egg->startup,
            //     $environment,
            //     $package->memory,
            //     $package->swap,
            //     $package->disk_space,
            //     $package->io,
            //     $package->cpu_limit,
            //     $package->databases,
            //     $package->backups,
            //     $allocation
            // );

            // $server = $create['attributes'];

            // $write_to_service = [
            //     'package_id' => $package->id,
            //     'docker_image' => $this->request->docker_image,
            //     'server_id' => $server['id'],
            //     'node_id' => $node->id,
            //     'user_id' => user()->id,
            //     'order_id' => $this->order->id
            // ];

            // // 创建成功, 写入
            // Service::create($write_to_service);
        }));


        return true;
    }

    public function cancel()
    {
        // 取消服务器
        $service = Service::where('order_id', $this->order->id)->firstOrFail();

        userHas($service);

        $access = new Access();

        $access->delete($service->server_id);

        $service->delete();

        return true;
    }

    public function suspend()
    {
        // 暂停服务器
        $service = Service::where('order_id', $this->order->id)->firstOrFail();

        userHas($service);

        $access = new Access();

        $access->suspend($service->server_id);

        return true;
    }

    public function unsuspend()
    {
        // 恢复服务器
        $service = Service::where('order_id', $this->order->id)->firstOrFail();

        userHas($service);

        $access = new Access();

        $access->unsuspend($service->server_id);

        return true;
    }


    public function createUserIfNotExists($email)
    {
    }
}
