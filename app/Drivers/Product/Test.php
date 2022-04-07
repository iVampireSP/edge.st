<?php

namespace App\Drivers\Product;

use App\Drivers\Driver;
use Exception;

class Test extends Driver
{
    // 创建使用这个 Driver 的产品后做出的动作
    public function hello()
    {
        return 'Hello~ This is a test product driver.';
    }

    public function suspend()
    {
        // throw new Exception();
        return 'suspended';
    }
}
