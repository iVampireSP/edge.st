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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('location')->nullable()->index();

            $table->string('type')->index();

            $table->string('driver')->nullable()->index();

            $table->json('extra')->nullable();

            $table->string('user')->index()->nullable();

            $table->string('password')->index()->nullable();

            $table->string('token')->index()->nullable();

            $table->string('callback_token')->index()->nullable();


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
        Schema::dropIfExists('servers');
    }
};
