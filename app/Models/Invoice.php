<?php

namespace App\Models;

use App\Traits\Model\Common;
use Illuminate\Support\Carbon;
use App\Exceptions\Invoice\Paid;
use App\Exceptions\Invoice\Overdue;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\User\BalanceNotEnough;
use Illuminate\Support\Facades\Log;

class Invoice extends Model
{

    // 扣费顺序
    // 1. 下单产品 App\Models\Product::userBuy(5, 1, 1, 'prepay')
    // 2. 支付订单 App\Models\Order::pay(15);
    // 3. 分情况
    // 4. ongoing: calcCurrentAmount, autoCost
    // 5. open: pay()
    use Common;

    protected $fillable = [
        'status', 'amount', 'user_id', 'amount_paid', 'order_id', 'comment'
    ];

    protected $casts = [
        'paid_at' => 'datetime'
    ];

    public static function createToOrder($order_id, $amount)
    {
        // 在订单下创建发票
    }

    public static function pay(self $invoice)
    {
        // 检查发票状态
        self::invoiceStatusFail($invoice);

        // 如果是提前支付
        if ($invoice->status == 'ongoing') {
            // 直接返回成功
            return self::calcCurrentAmount($invoice);
        }

        // $cost = $pay_amount;
        // if ($invoice->order->method) {
        //     // 按日支付
        //     // 产品价格 / $days_of_month

        //     // 这个模式下不启用按时间支付

        //     // // 获取本月有多少天
        //     // $days_of_month = Carbon::now()->endOfMonth()->day;
        //     // // 获取这次按时间支付时应该付的价格
        //     // $cost /= $days_of_month;
        // } else {
        //     // 一次性支付
        //     // $cost = $pay_amount;
        // }
        return self::userPayInvoice($invoice);
    }

    // 计算 current_amount
    public static function calcCurrentAmount(self $invoice)
    {
        $invoice->load('order');
        if ($invoice->status == 'ongoing') {
            // 按日支付
            // 产品价格 / $days_of_month

            // 这个模式下不启用按时间支付

            // // 获取本月有多少天
            $current_amount = $invoice->order->current_amount;
            $days_of_month = Carbon::now()->endOfMonth()->day;
            // // 获取这次按时间支付时应该付的价格
            $current_amount += $invoice->order->amount / $days_of_month;


            $invoice->order->current_amount = $current_amount;

            $invoice->order->save();

            // 扣费测试
            // self::userPayInvoice($invoice);

            return true;
        } else {
            return false;
        }
    }

    // 扣除所有 current_amount
    public static function autoCost()
    {
        // 在自动扣费失败后，会将订单标记为暂停
        // 订单暂停后，服务也会被暂停
        // 如果订单暂停超过7天，则关闭订单。请求关闭（退款）也是如此。


        self::where('status', 'ongoing')->with('order')->chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                //  计算
                self::calcCurrentAmount($invoice);

                // 检查订单是否关闭
                if ($invoice->order->status == 'suspended') {
                    // 订单关闭的话则不扣费
                    continue;
                }

                // 扣费
                $result = self::userPayInvoice($invoice);;

                if (!$result) {
                    Order::suspend($invoice->order);
                }
            }
        });

        // 调用 self::userPayInvoice()
    }


    public function createToUser($user_id, $amount)
    {
        // 给指定用户创建指定金额的发票
    }

    public function bindToOrder($invoice_id, $order_id)
    {
        // 将发票绑定给订单
    }

    public function bindToUser($invoice_id, $user_id)
    {
        // 将发票绑定给用户
    }


    public static function userPayInvoice(self $invoice)
    {
        $invoice->load('order');


        // 让发票所有者支付 （按照账单类型进行扣费，并返回给订单（如果可以的话））
        // 获取价格
        $pay_amount = $invoice->order->amount;
        // 扣费

        // 检测是否需要清空 current_amount
        $clear = false;
        if ($invoice->status == 'ongoing') {
            $clear = true;
            // 按时间计费的话则扣除 current_amount
            $pay_amount = $invoice->order->current_amount;
        } else {
            $invoice->paid_at = Carbon::now();
        }

        // 扣费之前检测: 是否达到最大amount
        $checkCostAmount = $pay_amount + $invoice->amount_paid;
        if ($checkCostAmount > $invoice->amount) {
            // 要扣费的加起来大于总共的情况
            // 重置为账单大小
            $pay_amount = $invoice->amount;
        }

        $cost = User::costBalance($pay_amount, $invoice->user_id);
        if (!$cost) {
            return false;
        }

        // 更新发票 已经支付的 amount
        $invoice->amount_paid += $pay_amount;

        // 判断发票会不会欠费
        if ($invoice->amount_paid < 0) {
            throw new Overdue();
        }

        $invoice->save();

        // 刷新
        $invoice->refresh();

        // 判断支付已经支付完全
        if ($invoice->amount_paid >= $invoice->amount) {
            $status = 'paid';
            $invoice->status = $status;
        }


        if ($clear) {
            // 没有问题后清空 current_amount
            $invoice->order->current_amount = 0;
            $invoice->order->save();
        }


        $invoice->save();

        return true;
    }


    // 如果发票欠费了，自动交付欠费的发票
    public function payArrears()
    {
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }


    public static function invoiceStatusFail(self $invoice)
    {
        if ($invoice->status == 'paid') {
            throw new Paid('Invoice paid.');
        }

        if ($invoice->status == 'suspended') {
            return false;
        }
    }
}
