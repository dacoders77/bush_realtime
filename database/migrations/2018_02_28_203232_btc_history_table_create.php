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
