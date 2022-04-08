<?php

namespace App\Models;

use App\Drivers\Driver;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Observers\OrderObserver;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Exceptions\Order\InvoiceEmpty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use App\Exceptions\Order\OngoingInvoice;
use App\Exceptions\Order\OrderCancelled;
use App\Exceptions\Controller\MethodNotFound;

class Order extends Model
{
    //

    use OrderObserver;

    public $fillable = [
        'order_id',
        'method',
        'mode',
        'type',
        'current_amount',
        'amount',
        'promo_type',
        'promo_value',
        'promo_code_id',
        'invoice_id',
        'user_id',
        'server_id',
        'product_id',
        'status',
        'ip',
        'note',
        'extra',
        'controller'
    ];

    protected $hidden = [
        'controller', 'order_id'
    ];


    protected $casts = [
        'suspended_at' => 'datetime'
    ];

    public static function createToUser(
        $user_id,
        $method,
        $mode,
        $type,
        // $current_amount, // 弃用，金额应该由下方程序计算
        $amount,
        $promo_code,
        $server_id,
        $product_id,
        $status,
        $controller
    ) {
        // 给用户创建订单
        $order_id = self::generateOrderId();

        // 本月的天数
        $days_of_month = Carbon::now()->endOfMonth()->day;
        $current_amount = $amount;
        if ($method) {
            // 按日支付
            // 产品价格 / $days_of_month
            $current_amount /= $days_of_month;
        } else {
            // 一次性支付
            // 不做任何处理
        }

        // 寻找优惠码




        // 写入订单
        $order = self::create([
            'order_id' => $order_id,
            'method' => $method,
            'mode' => $mode,
            'type' => $type,
            'current_amount' => $current_amount,
            'amount' => $amount,
            // 'promo_type' => $promo_type, // ToDo: 优惠码类型
            // 'promo_value' => $promo_value, // 优惠码优惠金额
            // 'promo_code_id' => $promo_code_id // 优惠码ID
            'user_id' => $user_id,
            'server_id' => $server_id,
            'product_id' => $product_id,
            'status' => $status,
            'ip' => Request::ip(),
            'controller' => $controller,
        ]);

        // 创建发票

        $status = 'open';

        if ($method) {
            $status = 'ongoing';
        }

        $invoice = Invoice::create([
            'status' => $status,
            'amount' => $amount,
            'user_id' => $user_id,
            'amount_paid' => 0,
            'order_id' => $order->id,
        ]);

        $order->invoice_id = $invoice->id;
        $order->save();

        return $order;
    }

    public static function bindToInvoice($order_id, $invoice_id)
    {
        // 将订单绑定到发票
    }

    public static function pay($order_id)
    {
        // 支付订单对应的发票

        // 从发票中寻找账单
        $invoice = Invoice::where('order_id', $order_id)->with('order')->first();

        if (is_null($invoice)) {
            throw new InvoiceEmpty('invoice not found');
        } else {
            if ($invoice->status == 'ongoing') {
                throw new OngoingInvoice('ongoing invoice can not manual pay');
            } else {
                Invoice::pay($invoice);
            }
        }

        return true;
    }

    // 生成唯一订单号
    public static function generateOrderId()
    {
        //今天时间
        $date = date('Ymd', time());
        //当天自增数
        $dateNum = Redis::hincrby($date, 1, 1);
        $dateNum = sprintf("%08d", $dateNum);
        //当天订单号
        $order_id = $date . $dateNum;
        //清除前天的数据
        $yesterdayDate = date('Ymd', time() - 86400 * 2);
        if (Redis::exists($yesterdayDate)) {
            Redis::del($yesterdayDate);
        }
        return $order_id;
    }

    public static function suspend(self $order, $operator = 'auto')
    {
        if ($order->status == 'suspended' && !is_null($order->suspended_at)) {
            throw new OrderCancelled('order is already suspended.');
        }

        try {
            Controller::call($order->controller, 'suspend', null, null, $order);
        } catch (MethodNotFound) {
            // 找不到暂停方法则直接暂停
        }

        // 将订单 status 设置为 suspend
        $order->status = 'suspended';
        $order->suspended_by = $operator;
        // 保存一次
        $order->save();


        // 暂停发票
        $order->load('invoice');

        $order->invoice->update(['status' => 'suspended']);

        $order->refresh();
        $order->suspended_at = Carbon::now();
        $order->save();

        return true;
    }


    public static function cancel(self $order)
    {

        if ($order->status == 'cancelled' && !is_null($order->cancelled_at)) {
            throw new OrderCancelled('order is already cancelled.');
        }
        try {
            Controller::call($order->controller, 'cancel', null, null, $order);
        } catch (MethodNotFound) {
            //
        }


        $order->status = 'cancelled';
        // 保存一次
        $order->save();

        // 关闭发票
        $order->load('invoice');

        // 付费发票
        Invoice::userPayInvoice($order->invoice);

        $order->invoice->update(['status' => 'paid']);

        $order->refresh();
        $order->cancelled_at = Carbon::now();
        $order->save();

        return true;
    }


    // 暂停 status 为 suspended 但是 没有 suspended_at 的订单
    // 如果发生以上情况，则是暂停失败
    public static function autoSuspend()
    {
        self::where('status', 'suspended')->where('suspended_at', null)->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                return self::suspend($order);
            }
        });
    }

    // 自动关闭 suspended_at 超过7天的订单
    public static function autoCancel()
    {
        self::where('status', 'suspended')->where('suspended_at', Carbon::parse('7 days ago'))->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                return self::cancel($order);
            }
        });
    }

    // 重试关闭失败的订单
    public static function retryCancel()
    {
        self::where('status', 'cancelled')->where('cancelled_at', null)->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                return self::cancel($order);
            }
        });
    }

    // 取消暂停
    public static function unsuspend(self $order)
    {
        if ($order->status == 'ongoing') {
            throw new OrderCancelled('order is already ongoing.');
        }

        try {
            Controller::call($order->controller, 'unsuspend', null, null, $order);
        } catch (MethodNotFound) {
            //
        }


        $order->status = 'ongoing';
        $order->suspended_at = null;
        // 保存一次
        $order->save();


        // 恢复发票
        $order->load('invoice');

        $order->invoice->update(['status' => 'ongoing']);

        $order->refresh();
        $order->suspended_at = null;
        $order->save();

        return true;
    }

    public static function autoUnsuspend()
    {
        self::where('status', 'suspended')->where('suspended_by', 'auto')->with('user')->chunk(100, function ($orders) {
            foreach ($orders as $order) {
                if ($order->user->balance >= 1) {
                    self::unsuspend($order);
                } else {
                    continue;
                }
            }
        });

        return true;
    }


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
