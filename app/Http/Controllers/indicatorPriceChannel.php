<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;


/**
 * Class indicatorPriceChannel
 * This class calculates price channel high, low values based on data read from DB.
 * Values are recorded (updated) back to DB when calculated.
 *
 * This class is called in 3 cases:
 * 1. On the first (initial) start of the application
 * 2. When the new bar is issued
 * 3. When Initial start button is clicked from GUI
 *
 * @package App\Http\Controllers
 */

class indicatorPriceChannel extends Controller
{

    /**
     * @param int elementIndex  Loop index. If there the price channel period is 5 this loop will go from 0 to 4.
     *                          The loop is started on each candle while go through all candled in array.
     */

    public function index() {

        $priceChannelPeriod = DB::table('settings')->where('id', env("SETTING_ID"))->value('price_channel_period');
        $elementIndex = 0;
        $priceChannelHighValue = 0;
        $priceChannelLowValue = 999999;


        /*
        DB::table('btc_history')
            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // id of the last record. desc - descent order
            ->update([
                'close' => $nojsonMessage[2][3],
                'high' => $this->barHigh,
                'low' => $this->barLow,
            ]);
        */

        echo "IndicatorPriceChannel.php Indicator recalculation started\n";


        $allDbRows = DB::table(env("ASSET_TABLE"))->orderBy('time_stamp', 'desc')->get(); // desc, asc - order. Read the whole table from BD to $allDbRows
        // desc - from big values to small
        // asc - from small to big
        // in this case: desc. [0] element is the last record in DB. and it's id - quantity of records


        // echo "dd: " . $allDbRows[1]->date; works good!

        // Calculate price channel max, min
        //First element in the array is the oldest
        foreach ($allDbRows as $z) { // Start from the oldes element in the array. The one on the left at the chart

            //echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($z[0] / 1000));
            //echo "<br>";

                      //  'time_stamp' => $z[0],
                      //  'open' => $z[1],
                      //  'close' => $z[2],
                      //  'high' => $z[3],
                      //  'low' => $z[4],
                      //  'volume' => $z[5],

                      //   $rowValue->open,


            // $requestBars >= $priceChannelPeriod - 1
            if ($elementIndex <= DB::table('settings')->where('id', env("SETTING_ID"))->value('request_bars') - $priceChannelPeriod - 1){ // We must stop before $requestBars untill the end of the array

                //echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($z[0] / 1000));
                //echo "<br>";

                // Cycle through elements ($price_channel_interval) for calculating min and max
                //for ($i = $elementIndex ; $i > 10; $i++)
                for ($i = $elementIndex ; $i < $elementIndex + $priceChannelPeriod; $i++)
                {
                    //print_r ($allDbRows[$i][3]);
                    //echo "<br>";

                    //echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($allDbRows[$i]->time_stamp / 1000)) . " " . $allDbRows[$i]->high;
                    //echo "<br>";


                    if ($allDbRows[$i]->high > $priceChannelHighValue) // Find max value in interval
                        $priceChannelHighValue = $allDbRows[$i]->high;

                    if ($allDbRows[$i]->low < $priceChannelLowValue) // Find low value in interval
                        $priceChannelLowValue = $allDbRows[$i]->low;


                }
                //echo "**" . $priceChannelHighValue;
                //echo "_______________________________<br>";


                DB::table(env("ASSET_TABLE"))
                    ->where('time_stamp', $allDbRows[$elementIndex]->time_stamp)
                    ->update([
                        'price_channel_high_value' => $priceChannelHighValue,
                        'price_channel_low_value' => $priceChannelLowValue,
                    ]);

                // Reset high, low price channel values
                $priceChannelHighValue = 0;
                $priceChannelLowValue = 999999;

            } // if

            $elementIndex++;

        } // foreach max, min



    }
}
