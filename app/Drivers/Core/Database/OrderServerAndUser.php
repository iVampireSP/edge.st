<?php

namespace App\Drivers\Core\Database;

use App\Drivers\Driver;

class OrderServerAndUser extends Driver
{

    public static function create($table, $driver = false)
    {
        $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();

        $table->foreignId('server_id')->references('id')->on('servers')->cascadeOnDelete();

        $table->foreignId('order_id')->references('id')->on('orders')->cascadeOnDelete();

        if ($driver) {
            $driver = str_replace('\\', '\\\\', $driver);
            $table->string('driver')->index()->default($driver);
        }
    }

    public static function createDriver($table, $driver)
    {
        $driver = str_replace('\\', '\\\\', $driver);
        $table->string('driver')->index()->default($driver);
    }
}
