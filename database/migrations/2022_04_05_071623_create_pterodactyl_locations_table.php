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
        Schema::create('pterodactyl_locations', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->string('short')->index();

            $table->unsignedBigInteger('location_id')->index()->nullable();

            $table->boolean('visibility')->index()->default(false);

            $table->unsignedBigInteger('node_count')->index()->default(0);

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
        Schema::dropIfExists('pterodactyl_locations');
    }
};
