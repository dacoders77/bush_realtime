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
    /**
     * @param int $param        Is set 1 by clicking Initial start buttin in GUI at the first page which means that
     *                          historical data must be recevied again and btc_histore table in DB - erased (truncated)

     */

    public function index($param){


        $timeframe =
            DB::table('settings')
                ->where('id', 1)
                ->value('time_frame') . "m";

        //$asset = "ETHBTC"; // BTCUSD ETHUSD ETHBTC
        $asset =
            DB::table('settings')
                ->where('id', 1)
                ->value('symbol');


        //echo DB::table('btc_history')->orderBy('time_stamp', 'desc')->first();
        echo "init start: " . DB::table('settings')->where('id', 1)->value('initial_start') . " param: " . $param . "<br>";

        // If the initial start is true. True is set by default or by Initial start button from the start page. Set to false after history data is loaded
        if ((DB::table('settings')->where('id', 1)->value('initial_start')) || $param == 1){

            echo "request bars: " . DB::table('settings')->where('id', 1)->value('request_bars');

            DB::table('btc_history')->truncate(); // Drop all records in the table

            // Create guzzle http client
            $api_connection = new Client([
                'base_uri' => 'https://api.bitfinex.com/v2/',
                'timeout' => 50 // If make this value small - fatal error occurs
            ]);

            //$restEndpoint = "candles/trade:" . $timeframe . ":t" . $asset . "/hist?limit=20&start=" . $start . "&end=" . $end . "&sort=1";
            $restEndpoint = "candles/trade:" . $timeframe . ":t" . $asset . "/hist?limit=" . DB::table('settings')->where('id', 1)->value('request_bars'); // Gets bars from the present moment. No dates needed. Values must be reversed befor adding to DB. Otherwise - the chart is not properly rendered, all bars look fat

            // http://docs.guzzlephp.org/en/stable/request-options.html#http-errors
            $response = $api_connection->request('GET', $restEndpoint, ['http_errors' => true ]);

            //echo "GUZZLE reason: " . $response->getReasonPhrase() . "<br>";

            $body = $response->getBody(); // Get the body out of the request
            $json = json_decode($body, true); // Decode JSON. Associative array will be outputed

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

            } // if 200

            else // Request is not successful. Error code is not 200

            {
                //echo "<script>alert('Request error: too many requests!' )</script>"; // $response->getReasonPhrase()
            }

            // Stet Initial flag to false
            DB::table('settings')
                ->where('id', 1)
                ->update([
                    'initial_start' => 0,
                ]);

        } // if initial_start

    } // index()

} // controller
