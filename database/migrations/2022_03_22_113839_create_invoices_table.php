<?php

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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            /* 发票状态码
             * open: 发票未付款
             * paid: 发票已付款
             * refunded: 已退款
             * ongoing: 持续支付(当已经支付的额度=需要支付的额度时标记为paid)
             * suspended: 暂停付款。多半用于超额情况。
             */
            $table->string('status')->index()->default('open');

            // 需要支付的额度
            $table->double('amount')->index();

            // 已经支付的额度
            $table->double('amount_paid')->index()->default(0);

            // 支付时间
            $table->dateTime('paid_at')->index()->nullable();
            // 退款时间
            $table->dateTime('refunded_at')->index()->nullable();

            $table->string('comment')->nullable();
            
            // 账单生成的页面可以包含多个订单

            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();

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
        Schema::dropIfExists('invoices');
    }
};
