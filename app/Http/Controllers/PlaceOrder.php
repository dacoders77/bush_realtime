<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class PlaceOrder extends Controller
{
    public function index() {

        // Second creation
        //$bit_fnx = new BitFnx(); // Created new instance of class where all headers brought together

        $restAuthEndpoint = "summary"; //Where we exactly send the request. REST AUTHENTICATED ENDPOINTS. It can be: summary, account_fees, key_info etc. https://docs.bitfinex.com/v1/reference#auth-key-permissions

// Create new instance of guzzle and pass $data array as the set of headers
        $z = $bit_fnx->get_account_infos($restAuthEndpoint);


// Output to the screen
        echo "****************payload: " . $bit_fnx->pay . "<br>";
        echo "****************signature: " . $bit_fnx->sig . "<br>";


        $api_connection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v1/',
            'timeout' => 5 // If make this value small - fatal error occurs
        ]);

    } // public
}
