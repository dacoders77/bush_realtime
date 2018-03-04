<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SettingsTableCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('initial_start');
            $table->integer('time_frame');
            $table->integer('request_bars');
            $table->integer('price_channel_period');
        });

        DB::table('settings')->insert(array(
            'initial_start' => 1,
            'time_frame' => 5,
            'request_bars' => 30,
            'price_channel_period' => 3
        ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
