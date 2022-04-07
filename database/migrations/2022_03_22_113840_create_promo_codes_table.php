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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();

            // 代码
            $table->string('code')->index();

            // 类型
            $table->string('type')->default('percentage'); // 百分比: percentage 固定: static

            $table->unsignedDouble('amount')->index();

            // 使用次数
            $table->unsignedInteger('times')->default(0);

            // 到期时间
            $table->dateTime('expired_at')->index()->nullable();


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
        Schema::dropIfExists('promo_codes');
    }
};
