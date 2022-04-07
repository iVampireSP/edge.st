<?php

namespace App\Traits\Model;

trait Common
{
    public static function own($user_id = null, $space_id = null)
    {
        $orm = self::where('user_id', $user_id ?? auth('api')->id());

        if ($space_id) {
            $orm->where('space_id', $space_id);
        }

        return $orm;
    }
}
