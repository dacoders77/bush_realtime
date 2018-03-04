<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;



class LoadDataFromDB extends Controller
{
    public function index(){

        $allDbValues = DB::table('btc_history')->get(); // Read the whole table from BD to $allDbValues

        foreach ($allDbValues as $rowValue) { // Go through the records read from DB

            $candles[] = [
                $rowValue->time_stamp,
                $rowValue->open,
                $rowValue->high,
                $rowValue->low,
                $rowValue->close,
            ];

            //$rowValue->price_channel_high_value,
            //$rowValue->price_channel_low_value

            $priceChannelHighValue[] = [
                $rowValue->time_stamp,
                $rowValue->price_channel_high_value
            ];

            $priceChannelLowValue[] = [
                $rowValue->time_stamp,
                $rowValue->price_channel_low_value
            ];
        }

        $seriesData = [$candles, $priceChannelHighValue, $priceChannelLowValue];
        return $seriesData;

    }
}
