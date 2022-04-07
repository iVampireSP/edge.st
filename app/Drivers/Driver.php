<?php

namespace App\Drivers;

use App\Exceptions\Driver\MethodNotFound;
use App\Models\Order;

class Driver
{
    public static function canCall(self | string $driver, $method)
    {

        // 检测Driver
        if (method_exists($driver, $method)) {
            return true;
        } else {
            throw new MethodNotFound('Driver method not found.');
        }
    }

    public static function call(self | string $driver, $method, $data = null)
    {
        self::canCall($driver, $method);

        $driver = new $driver();

        return call_user_func([$driver, $method], $data);
    }
}
