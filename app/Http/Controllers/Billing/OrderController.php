<?php

namespace App\Http\Controllers\Billing;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    // 订单控制器

    public function index()
    {
        $orders = Order::where('user_id', user()->id)->with(['invoice', 'product'])->get();

        return $orders;
    }

    // public function store() {

    // }

    public function pay($order_id)
    {
        $order = Order::find($order_id);

        userHas($order);

        Order::pay($order->id);

        return success();
    }

    public function cancel($order_id)
    {
        $order = Order::find($order_id);

        userHas($order);

        Order::cancel($order);

        return success();
    }
}
