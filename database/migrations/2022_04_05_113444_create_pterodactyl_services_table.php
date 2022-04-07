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
        Schema::create('pterodactyl_services', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('package_id')->index();
            $table->foreign('package_id')->references('id')->on('pterodactyl_packages');

            $table->string('docker_image')->index();

            $table->unsignedBigInteger('node_id')->index();
            $table->foreign('node_id')->references('id')->on('pterodactyl_nodes');

            $table->unsignedBigInteger('server_id')->index();

            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('pterodactyl_services');
    }
};
