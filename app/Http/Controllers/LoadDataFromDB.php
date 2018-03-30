<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;



class LoadDataFromDB extends Controller
{

    public function index(){

        $longTradeMarkers[] = "";
        $shortTradeMarkers[] = "";

        $allDbValues = DB::table(env("ASSET_TABLE"))->get(); // Read the whole table from BD to $allDbValues

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

            // Add long trade markers
            if ($rowValue->trade_direction == "buy") {
                $longTradeMarkers[] = [
                    $rowValue->time_stamp,
                    $rowValue->trade_price
                ];
            }

            // Add short trade markers
            if ($rowValue->trade_direction == "sell") {
                $shortTradeMarkers[] = [
                    $rowValue->time_stamp,
                    $rowValue->trade_price
                ];
            }


        }

        //              0                   1                       2                   3                   4
        $seriesData = [$candles, $priceChannelHighValue, $priceChannelLowValue, $longTradeMarkers, $shortTradeMarkers];
        return $seriesData;

    }
}
