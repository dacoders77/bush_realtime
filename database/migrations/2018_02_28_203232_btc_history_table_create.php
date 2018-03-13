<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BtcHistoryTableCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('btc_history', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date')->nullable(); // Use nullable if this field can be blank
            $table->bigInteger('time_stamp')->nullable();
            $table->float('open')->nullable();
            $table->float('close')->nullable();
            $table->float('high')->nullable();
            $table->float('low')->nullable();
            $table->float('volume')->nullable();
            $table->float('price_channel_high_value')->nullable();
            $table->float('price_channel_low_value')->nullable();
            $table->dateTime('trade_date')->nullable();
            $table->float('trade_price')->nullable();
            $table->double('trade_commission')->nullable();
            $table->double('accumulated_commission')->nullable();
            $table->string('trade_direction')->nullable();
            $table->double('trade_volume')->nullable();
            $table->double('trade_profit')->nullable();
            $table->double('accumulated_profit')->nullable();
            $table->double('net_profit')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('btc_history');
    }
}
