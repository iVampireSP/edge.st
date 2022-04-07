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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->string('description')->nullable()->index();

            $table->boolean('hidden')->index()->default(0);
            $table->boolean('feature')->index()->default(0);

            $table->double('price')->index()->nullable();

            // $table->json('validate')->nullable();
            $table->foreignId('product_id')->nullable()->index()->references('id')->on('products')->cascadeOnDelete();

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
        Schema::dropIfExists('products');
    }
};
