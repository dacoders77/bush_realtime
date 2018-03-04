<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client; // Guzzle is used to send http headers http://docs.guzzlephp.org/en/stable/
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;


/**
 * Class HistoryFinex
 * Reads the history data from www.bitfinex.com and records in to local DB
 * @package App\Http\Controllers
 */
class HistoryFinex extends Controller
{

    public function index(){

        $requestBars = 100;

        $priceChannelPeriod = 10;
        $elementIndex = 0; // Index for loop through all elements in interval for which price channel is calculated

        $priceChannelHighValue = 0;
        $priceChannelLowValue = 999999;

        DB::table('btc_history')->truncate(); // Drop all records in the table

        $timeframe = "1m";
        $asset = "BTCUSD";

        // Create guzzle http client
        $api_connection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v2/',
            'timeout' => 50 // If make this value small - fatal error occurs
        ]);

        //$restEndpoint = "candles/trade:" . $timeframe . ":t" . $asset . "/hist?limit=20&start=" . $start . "&end=" . $end . "&sort=1";
        $restEndpoint = "candles/trade:" . $timeframe . ":t" . $asset . "/hist?limit=" . $requestBars; // Gets bars from the present moment. No dates needed. Values must be reversed befor adding to DB. Otherwise - the chart is not properly rendered, all bars look fat

        // http://docs.guzzlephp.org/en/stable/request-options.html#http-errors
        $response = $api_connection->request('GET', $restEndpoint, ['http_errors' => true ]);

        //echo "GUZZLE reason: " . $response->getReasonPhrase() . "<br>";

        $body = $response->getBody(); // Get the body out of the request
        $json = json_decode($body, true); // Decode JSON. Associative array will be outputed

        //echo "<pre>";
        //print_r($response);
        //echo "</pre>";

        if ($response->getStatusCode() == 200) // Request successful
        {
            // Add candles to DB
            foreach (array_reverse($json) as $z) { // The first element in array is the youngest - first from the left on the chart. Go through the array backwards. This is the order how points will be read from DB and outputed to the chart


                DB::table('btc_history')->insert(array( // Record to DB
                    'date' => gmdate("Y-m-d G:i:s", ($z[0] / 1000)), // Date in regular format. Converted from unix timestamp
                    'time_stamp' => $z[0],
                    'open' => $z[1],
                    'close' => $z[2],
                    'high' => $z[3],
                    'low' => $z[4],
                    'volume' => $z[5],
                ));
            }

                //First element in the array is the oldest
                //echo "el:" . gmdate("Y-m-d G:i:s", ($z[0] / 1000)) . "<br>";

            foreach ($json as $z) { // Start from the oldes element in the array. The one on the left at the chart

                //echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($z[0] / 1000));
                //echo "<br>";

                // $requestBars >= $priceChannelPeriod - 1
                if ($elementIndex <= $requestBars - $priceChannelPeriod - 1){ // We must stop before $requestBars untill the end of the array

                    //echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($z[0] / 1000));
                    //echo "<br>";

                    // Cycle through elements ($price_channel_interval) for calculating min and max
                    //for ($i = $elementIndex ; $i > 10; $i++)
                    for ($i = $elementIndex ; $i < $elementIndex + $priceChannelPeriod; $i++)
                    {
                        //print_r ($json[$i][3]);
                        //echo "<br>";

                        echo $elementIndex . " " . gmdate("Y-m-d G:i:s", ($json[$i][0] / 1000)) . " " . $json[$i][3];
                        echo "<br>";


                        if ($json[$i][3] > $priceChannelHighValue) // Find max value in interval
                            $priceChannelHighValue = $json[$i][3];

                        if ($json[$i][4] < $priceChannelLowValue) // Find low value in interval
                            $priceChannelLowValue = $json[$i][4];



                    }
                    echo "**" . $priceChannelHighValue;
                    echo "_______________________________<br>";



                    DB::table('btc_history')
                        ->where('time_stamp', $json[$elementIndex][0])
                        ->update([
                            'price_channel_high_value' => $priceChannelHighValue,
                            'price_channel_low_value' => $priceChannelLowValue,
                        ]);

                    // Reset high, low price channel values
                    $priceChannelHighValue = 0;
                    $priceChannelLowValue = 999999;

                } // if

                $elementIndex++;

            } // foreach

        } // if 200

        else // Request is not successful. Error code is not 200

        {
            //echo "<script>alert('Request error: too many requests!' )</script>"; // $response->getReasonPhrase()
        }

    }

}
