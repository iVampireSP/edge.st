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
        Schema::create('pterodactyl_nodes', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();

            $table->boolean('maintenance_mode')->index()->default(false);

            // $table->unsignedBigInteger('location_id')->index();

            $table->boolean('visibility')->index()->default(false);

            $table->unsignedInteger('memory')->index();

            $table->unsignedBigInteger('location_id')->index();
            $table->foreign('location_id')->references('id')->on('pterodactyl_locations');

            $table->unsignedBigInteger('node_id')->index()->nullable();

            $table->unsignedBigInteger('server_count')->index()->default(0);

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
        Schema::dropIfExists('pterodactyl_nodes');
    }
};
