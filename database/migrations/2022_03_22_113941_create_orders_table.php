<?php

use App\Drivers\Core\Database\OrderServerAndUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // 订单
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // 订单唯一编号
            $table->string('order_id')->index();

            // 计费方式： 1为按日，0为一次性支付
            $table->boolean('method')->default(1);

            // 支付模式(预付费 prepay 和后付费 postpaid )
            $table->string('mode')->index();

            // 类型(新购buy/退款refund)
            $table->string('type')->index();

            // 应支付的，每次创建账单并支付成功后都会归零
            // 这样做是为了用户提前关闭机器而造成不扣费的问题
            // 适合用于按量和按时间计费
            $table->double('current_amount')->index()->default(0);

            // 计费系统应该先根据账单然后来扣费，而不是根据订单
            // 应该支付的总价格
            $table->decimal('amount', 16, 2)->default(0);

            // 优惠方式: One Time Percentage(一次性)
            $table->string('promo_type')->nullable()->index()->default('One Time Percentage');
            // 优惠金额
            $table->decimal('promo_value', 16, 2)->default(0);

            // 关联
            $table->foreignId('promo_code_id')->nullable()->references('id')->on('promo_codes')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('server_id')->nullable()->references('id')->on('servers')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->references('id')->on('products')->cascadeOnDelete();

            // 使用 Controller
            $table->string('controller')->index()->nullable();

            /* 订单状态码
             * open: 订单打开
             * cancelled: 订单取消,将不会产生计费
             * failed: 订单失败，同关闭
             * suspended: 订单暂停，无法使用服务
             */
            $table->string('status')->default('open')->index();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('suspended_at')->nullable();

            // 打开订单时的IP
            $table->string('ip')->index()->nullable();

            $table->text('note')->nullable();

            // 保存产品的一些信息
            $table->json('extra')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
