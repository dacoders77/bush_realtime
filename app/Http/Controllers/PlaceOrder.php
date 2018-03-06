<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;


class PlaceOrder extends Controller
{

    public function index() {

        // Second creation
        $bit_fnx = new BitFnx(); // Created new instance of class where all headers brought together

        //Where we exactly send the request. REST AUTHENTICATED ENDPOINTS.
        // It can be:
        // summary
        // account_fees
        // key_info
        // etc https://docs.bitfinex.com/v1/reference#auth-key-permissions
        $restAuthEndpoint = "order/new";

        // Create new instance of guzzle and pass $data array as the set of headers
        // 3 vvalues are sent: X-BFX-APIKEY, X-BFX-PAYLOAD, X-BFX-SIGNATURE
        $z = $bit_fnx->get_account_infos($restAuthEndpoint); // Function get_account_infos() call and passing $restAuthEndpoint to it as a parameter
        print_r ($z);


        //echo "****************payload: " . $bit_fnx->pay . "<br>";
        //echo "****************signature: " . $bit_fnx->sig . "<br>";


        $api_connection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v1/',
            'timeout' => 5 // If make this value small - fatal error occurs
        ]);

        $response = $api_connection->request('POST', $restAuthEndpoint, [
            'headers' => [
                'X-BFX-APIKEY' => $bit_fnx->api_key,
                'X-BFX-PAYLOAD' => $bit_fnx->pay,
                'X-BFX-SIGNATURE' => $bit_fnx->sig
            ]
        ]); //https://api.bitfinex.com/v1/account_infos

        $body = $response->getBody(); // Get the body out of the request

        echo "<pre>";
        //print_r($response);
        //print_r (get_class_methods($response));
        //echo "body: " . $body;
        print_r(json_decode($body));





    } // public




}
