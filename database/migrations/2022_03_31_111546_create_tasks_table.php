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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            /*
             * 状态码
             * success: 成功
             * failed: 失败
             * running: 运行中
            */
            $table->string('status')->index();

            $table->string('comment')->index();

            // 进度
            $table->unsignedTinyInteger('progress')->default(0)->index();

            $table->foreignId('order_id')->nullable()->references('id')->on('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->references('id')->on('users')->cascadeOnDelete();

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
        Schema::dropIfExists('tasks');
    }
};
