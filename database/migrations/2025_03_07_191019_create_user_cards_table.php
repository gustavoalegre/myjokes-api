<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('stripe_id');
            $table->string('card_last_digits');
            $table->string('card_brand');
            $table->integer('joke_user_id')->unsigned();
            $table->foreign('joke_user_id')->references('id')->on('joke_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_cards');
    }
}
