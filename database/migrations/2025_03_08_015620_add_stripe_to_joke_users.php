<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeToJokeUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('joke_users', function (Blueprint $table) {
            // Agregar campo stripe_id para el customer
            $table->string('stripe_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('joke_users', function (Blueprint $table) {
            // Eliminar campo stripe_id
            $table->dropColumn('stripe_id');
        });
    }
}
