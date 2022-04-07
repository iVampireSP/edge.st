<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    // 产品控制器
    public function index()
    {
        $products = (new Product())->get();

        return success($products);
    }

    // 产品详细信息
    public function show(Request $request, $product)
    {
        $product = Product::findOrFail($product);
        // dd($product);

       
        $response = [
            'product' => $product,
        ];

        if ($product->controller) {
            $create = Controller::call($product->controller, 'create', $request, $product)->original['data'];
            $response['create'] = $create;

        }
        
        return success($response);
    }
    // public function show(Product $product) {
    //     dd($product);
    //     return $product;
    // }

    // 购买
    public function store(Request $request, $product)
    {
        $product = Product::findOrFail($product);
        // dd($product->controller);

        // 测试参数
        // Controller::call($product->controller, 'test', $request, $product);

        // 设置产品
        $order = Product::userBuy($product, user()->id, 1, 'prepaid');

        Controller::call($product->controller, 'setup', $request, $product, $order);

        return success($order);
    }
}
