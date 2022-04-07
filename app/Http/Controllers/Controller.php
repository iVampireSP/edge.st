<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Exceptions\Controller\MethodNotFound;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        return failed($errors, 'validate_failed');
    }


    public static function canCall(self | string $controller, $method)
    {

        if (method_exists($controller, $method)) {
            return true;
        } else {
            throw new MethodNotFound('Controller method not found.');
        }
    }

    public static function call(self | string $controller, $method, Request $request = null, Product $product = null, Order $order = null, $data = null)
    {
        $controller = reverse_slash($controller);

        self::canCall($controller, $method);

        $controller = new $controller();

        $controller->request = $request;
        $controller->product = $product;
        $controller->order = $order;

        return call_user_func([$controller, $method], $data);
    }
}
