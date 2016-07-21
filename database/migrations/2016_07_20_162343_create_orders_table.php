<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('advcampaign_id')->unsigned()->index(); //
            $table->bigInteger('order_id')->unsigned()->index(); //
            $table->string('status');
            $table->string('cart');
            $table->string('currency');
            $table->timestamp('action_date');
            $table->json('additional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('orders');
    }
}
