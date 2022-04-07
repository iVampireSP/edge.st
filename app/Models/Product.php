<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\Product\SellOutException;
use App\Exceptions\Product\NotFoundException;
use App\Exceptions\Product\CannotUseException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use App\Exceptions\User\NotFoundException as UserNotFoundException;
use Exception;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'product_id', 'price', 'controller',
    ];

    protected $hidden = [
        'controller'
    ];

    public static function userBuy(Product $product, $user_id, $method, $mode)
    {
        // 使产品被用户购买

        if (!$product) {
            throw new NotFoundException('Product not found');
        } elseif (is_null($product->product_id) || is_null($product->price)) {
            throw new CannotUseException('Can not use this product.');
        }

        if ($product->stock <= 0) {
            throw new SellOutException('Product is sold out.');
        }


        $user = User::find($user_id);

        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        // 按日支付和后付费时应该要动态调整 current_amount(应支付的)

        // ToDo: 后付费
        // if ($mode = 'prepay') {
        //     // 预付费
        //     // 不做任何处理
        // } else {
        //     // 后付费
        //     // 暂时不做任何处理
        // }

        $lock = Cache::lock("product_{$product->id}_lock", 5);
        $lock->block(5);
        try {
            // 减少库存
            $product->stock -= 1;
            $product->save();
        } finally {
            optional($lock)->release();
        }

        // 创建订单
        $order = Order::createToUser(
            $user->id,
            $method,
            $mode,
            'buy',
            // null, // 价格计算转到订单中而不是产品
            $product->price,
            null,
            null,
            $product->id,
            'open',
            $product->controller
        );



        return $order;


        // ToDo: 每个产品可能需要额外设置extra 或者 note 字段，他们将会被写入订单中

    }
}
