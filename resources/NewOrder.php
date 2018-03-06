<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 12/18/2017
 * Time: 7:40 PM
 */
//echo 'Hello. Lets get started!'; // It works good
//echo  '';
class bitfinex
{
    private $apikey;
    private $secret;
    private $url = "https://api.bitfinex.com";

    public function __construct($apikey, $secret) // Constructor
    {
        $this->apikey = $apikey;
        $this->secret = $secret;
    }

    // method
    public function  new_order($symbol, $amount, $bitfinex, $side, $type)
    {
        $request = "/v1/order/new";
        $data = array(
            "request" => $request,
            "symbol" => $symbol,
            "amount" => $amount,
            "exchange" => $bitfinex,
            "side" => $side,
            "type" => $type
        );
        return $this -> hash_request($data);
    }



    // method
    private function headers()
    {

        $data["nonce"] = strval(round(microtime(true) * 10, 0));
        echo var_dump($data) . "<br><br>";
        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac("sha384", $payload, $this->secret);
        return array(
            "X-BFX-APIKEY: " . $this->apikey,
            "X-BFX-PAYLOAD: " . $payload,
            "X-BFX-SIGNATURE: " . $signature
        );

    }

    // method
    private function hash_request($data)
    {
        $ch = curl_init();
        $bfurl = $this->url . $data["request"];
        $headers = $this->headers($data);
        curl_setopt_array($ch, array(
            CURLOPT_URL => $bfurl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => ""
        ));
        $ccc = curl_exec($ch); // Execute CURL
        return json_decode($ccc, true);
    }
}

//require_once ('config.php');
$trade = new bitfinex("CNW6skxiMWZpjwlUkKOaOZybFeW5TGZ5CG6dJP6yaDX","2pFOcoFYsio6t7VfPzW3aDRPGMUkX0TUFPkO0db2ZFZ"); // Api key, secret key
$buy = $trade->new_order("BTCUSD","0.001","bitfinex", "sell", "exchange market" );
print_r($buy);







