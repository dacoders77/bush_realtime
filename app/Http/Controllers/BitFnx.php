<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 3/6/2018
 * Time: 1:15 AM
 */

namespace App\Http\Controllers;


class BitFnx
{
    public $pay;
    public $sig;

    const API_URL = 'https://api.bitfinex.com'; // While sniffering use http instead of https. It lets you to see unencoded traffic otherwise you see unreadable set of characters

    public $api_key = "YcGqIAMGDgTVESLBDTTGQ32Q9DTsL0u5oY33GegN2wS";
    private $api_secret = "H4G2JdRGvsJ0JOKb1GcnDvoC27oVJvN5OU4hz4rlQMl";
    private $api_version = "v1";


    // Account info
    public function get_account_infos($summary) {

        $request = $this->endpoint($summary); // This method call can take two parameters as shown below
        // No params taken. Only first value is sent

        $data = array(
            'request' => $request, // Params MUST go here

            'symbol'=> 'BTCUSD', // ETHUSD ETCUSD
            'amount' => '0.004', // 0.02
            'price' => '1000',
            'exchange' => 'bitfinex',
            'side' => 'sell',
            'type' => 'market' // exchange market - exchanger order. market - margin order. If you need to open a short position - use 'market'. It is impossible to go short with 'exchange order'
                               // Funds must be located at Margin wallet if you go short and long.
                               // New order: https://bitfinex.readme.io/v1/reference#rest-auth-new-order
        );

        return $this->send_auth_request($data);
    }

    // End point and api version
    private function endpoint($method, $params = NULL) {
        $parameters = '';

        if ($params !== NULL) {
            $parameters = '/';

            if (is_array($params)) {
                $parameters .= implode('/', $params);
            } else {
                $parameters .= $params;
            }
        }

        return "/{$this->api_version}/$method$parameters";
    }

    /**
     * Prepare Header
     *
     * Add data to header for authentication purpose
     *
     * @param array $data
     * @return json
     */
    private function prepare_header($data)
    {
        $data['nonce'] = (string) number_format(round(microtime(true) * 100000), 0, '.', '');



        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha384', $payload, $this->api_secret);

        $this->pay = $payload;
        $this->sig = $signature;

        return array(
            'X-BFX-APIKEY: ' . $this->api_key,
            'X-BFX-PAYLOAD: ' . $payload,
            'X-BFX-SIGNATURE: ' . $signature
        );
    }


    /**
     * Send Signed Request
     *
     * Send a signed HTTP request
     *
     * @param array $data
     * @return mixed
     */
    private function send_auth_request($data)
    {
        //$ch = curl_init();
        //$url = self::API_URL . $data['request']; // This is an array. Only 'request' field is taken. in other cases many fields can exist. https://api.bitfinex.com/v1/account_infos

        $headers = $this->prepare_header($data);

        echo "<pre>";
        //print_r($headers);
        //var_dump(curl_getinfo($ch));
        //print_r (curl_exec($ch));
        echo "</pre>";

        //var_dump($headers); // Not null!
        return $headers;
    }

}


function num_to_string($num) {
    return number_format($num, 2, '.', '');
}
