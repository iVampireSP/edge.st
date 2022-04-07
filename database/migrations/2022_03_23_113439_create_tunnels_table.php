<?php

use App\Drivers\Application\Frp;
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
        Schema::create('tunnels', function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();

            $table->char('protocol', 5)->index()->default("tcp");

            $table->string('custom_domain')->nullable()->index();

            $table->string('local_address')->index();

            $table->unsignedSmallInteger('remote_port')->index()->nullable();

            $table->string('client_token')->index()->unique();

            $table->string('sk')->index()->nullable();

            $table->boolean('status')->default(false)->index();


            OrderServerAndUser::create($table, Frp::class);

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
        Schema::dropIfExists('tunnels');
    }
};
