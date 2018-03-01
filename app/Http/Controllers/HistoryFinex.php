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

        DB::table('btc_history')->truncate(); // Drop all records in the table

        $start = mktime(20, 00, 00, 03, 01, 2018) * 1000;
        $end = mktime(23, 00, 00, 03, 01, 2018) * 1000;
        $timeframe = "1m";
        $asset = "BTCUSD";

        // Create guzzle http client
        $api_connection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v2/',
            'timeout' => 50 // If make this value small - fatal error occurs
        ]);

        $restEndpoint = "candles/trade:" . $timeframe . ":t" . $asset . "/hist?limit=20&start=" . $start . "&end=" . $end . "&sort=1";

        echo "endpoint: " . $restEndpoint . "<br>";

        // http://docs.guzzlephp.org/en/stable/request-options.html#http-errors
        $response = $api_connection->request('GET', $restEndpoint, ['http_errors' => true ]);

        echo "GUZZLE reason: " . $response->getReasonPhrase() . "<br>";

        $body = $response->getBody(); // Get the body out of the request
        $json = json_decode($body, true); // Decode JSON. Associative array will be outputed

        //echo "<pre>";
        //print_r($response);
        //echo "</pre>";

        if ($response->getStatusCode() == 200) // Request successful
        {
            $i = 1;
            foreach ($json as $z) {

                echo '<pre>';
                echo $z[0] . " ";
                echo $i . " " . gmdate("d-m-Y G:i:s", ($z[0] / 1000));
                echo '</pre>';
                $i++;


                DB::table('btc_history')->insert(array(
                    'date' => gmdate("Y-m-d G:i:s", ($z[0] / 1000)), // Date in regular format. Converted from unix timestamp
                    'time_stamp' => $z[0],
                    'open' => $z[1],
                    'close' => $z[2],
                    'high' => $z[3],
                    'low' => $z[4],
                    'volume' => $z[5],
                ));

            } // foreach

            session()->flash('notif', 'The historical data loaded successfully! ' . $i . ' candles in total. Last loaded date: ');

        } // if 200

        else // Request is not successful. Error code is not 200

        {
            echo "<script>alert('Request error: too many requests!' )</script>"; // $response->getReasonPhrase()
        }


    }



}
