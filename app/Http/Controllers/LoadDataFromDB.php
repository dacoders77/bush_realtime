<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;



class LoadDataFromDB extends Controller
{
    public function index(){

        $allDbValuse = DB::table('btc_history')->get(); // Read the whole table from BD to $allDbValuse

        foreach ($allDbValuse as $rowValue) { // Go through the records read from DB

            $data[] = [$rowValue->time_stamp, $rowValue->open, $rowValue->high, $rowValue->low, $rowValue->close];
        }

        $seriesData = [$data];
        return $seriesData;

    }
}
