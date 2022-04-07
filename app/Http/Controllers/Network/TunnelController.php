<?php

namespace App\Http\Controllers\Network;

use App\Drivers\Application\Frp;
use App\Drivers\Platform\SakuraPanel\OpenFrp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TunnelController extends Controller
{
    public function index()
    {
        return success(
            'tunnel',
            'hi'
        );
    }

    public function getNode(Request $request) {
        // 获取节点列表
        
    }

    public function store(Request $request)
    {
        // dd($request);
        // 本地验证一遍
        $this->validate($request, [
            'name' => 'required',
            'protocol' => 'string',
            'local_address' => 'required',
            'server_id' => 'required',
        ]);

        // 调用驱动

        // 注册驱动配置
        $openfrp = new OpenFrp([]);

        // 驱动器验证
        $openfrp_validated = $openfrp->validate(0, 0);

        if ($openfrp_validated) {
            // 创建
            $openfrp->create(0, 0);
        }


        // 发送实时信息
        write(0);

        // 成功返回
        return success();
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
}
