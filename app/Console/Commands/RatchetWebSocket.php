<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\eventTrigger; // Linked the event

class RatchetWebSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratchet:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ratchet/pawl websocket client console application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "*****Ratchet websocket console command(app) started!*****\n";

        // The code from: https://github.com/ratchetphp/Pawl

        $loop = \React\EventLoop\Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, [
            'dns' => '8.8.8.8', // Does not work through OKADO inernet provider. Timeout error
            'timeout' => 10
        ]);
        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);

        $connector('wss://api.bitfinex.com/ws/2', [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) {
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn) {
                    //echo "Received: {$msg}\n";

                    //$conn->close(); // Connection close
                    //event(new eventTrigger("" . $msg)); // Fire new event. Events are located in app/Events
                    //var_dump($msg);
                    //echo json_decode($msg, true)

                    RatchetWebSocket::out($msg);

                });

                $conn->on('close', function($code = null, $reason = null) {
                    echo "Connection closed ({$code} - {$reason})\n";
                });

                //$conn->send(['event' => 'ping']);
                $z = json_encode([
                    //'event' => 'ping', // 'event' => 'ping'
                    'event' => 'subscribe',
                    'channel' => 'trades',
                    'symbol' => 'tBTCUSD'
                ]);
                $conn->send($z);

            }, function(\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";
                $loop->stop();
            });

        $loop->run();

    }

    // 'te', 'tu' Flags explained http://blog.bitfinex.com/api/websocket-api-update/
    // 'te' - When the trades is regeristed at the exchange
    // 'tu' - When the actual trade has happened. Delayed for 1-2 seconds from 'te'
    // 'hb' - Heart beating. If there is no new message in the channel for 1 second, Websocket server will send you an heartbeat message in this format
    // SNAPSHOT (the initial message) https://docs.bitfinex.com/docs/ws-general

    public function out($message)
    {

        $jsonMessage = json_decode($message->getPayload(), true); // Methods http://socketo.me/api/class-Ratchet.RFC6455.Messaging.MessageInterface.html
        //print_r($jsonMessage);
        //print_r(array_keys($z));

        //echo $message->__toString() . "\n"; // Decode each message

        if (array_key_exists('chanId',$jsonMessage)){
            echo "";
            //echo "***** parsed chanId: " . $jsonMessage['chanId'] . "\n";
            $chanId = $jsonMessage['chanId']; // Parsed channel ID then we are gonna listen exactley to this channel number. It changes each time you make a new connection
        }


        $nojasonMessage = json_decode($message->getPayload());
        if (!array_key_exists('event',$jsonMessage)){ // All messages excep first two associated arrays
            if ($nojasonMessage[1] == "te") // Only for the messages with 'te' flag. The faster ones
            {
                echo "id: " . $nojasonMessage[2][0];
                echo " date: " . gmdate("Y-m-d G:i:s", ($nojasonMessage[2][1] / 1000));
                echo " volume: " . $nojasonMessage[2][2];
                echo " price: " . $nojasonMessage[2][3] . "\n";

                $messageArray['tradeId'] = $nojasonMessage[2][0];
                //$messageArray['tradeDate'] = $nojasonMessage[2][1]; // gmdate("Y-m-d G:i:s", ($nojasonMessage[2][1] / 1000))
                $messageArray['tradeDate'] = $nojasonMessage[2][1];
                $messageArray['tradeVolume'] = $nojasonMessage[2][2];
                $messageArray['tradePrice'] = $nojasonMessage[2][3];

                event(new eventTrigger($messageArray)); // Fire new event. Events are located in app/Events

            }

        }


    }
}
