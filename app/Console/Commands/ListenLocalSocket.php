<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\eventTrigger; // Linked the events
use Illuminate\Support\Facades\DB;

class ListenLocalSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ratchet:socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listens to local c# web socket server at 0.0.0.0:8181';

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
        echo "*****Ratchet websocket c# local listener started!*****\n";

        // The code from: https://github.com/ratchetphp/Pawl

        $loop = \React\EventLoop\Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, [
            'dns' => '0.0.0.0', // Does not work through OKADO inernet provider. Timeout error
            'timeout' => 10
        ]);

        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);

        $connector('ws://localhost:8181', [], ['Origin' => '127.0.0.1:7451'])
            ->then(function(\Ratchet\Client\WebSocket $conn) {
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn) {

                    //RatchetWebSocket::out($msg); // Call the function when the event is received
                    echo $msg . "\n";

                });


                $conn->on('close', function($code = null, $reason = null) {
                    echo "Connection closed ({$code} - {$reason})\n";
                });

                $conn->send(['event' => 'ping']);
                $z = json_encode([
                    //'event' => 'ping', // 'event' => 'ping'
                    'event' => 'subscribe',
                    'channel' => 'trades',
                    'symbol' => 'tETHUSD' // tBTCUSD
                ]);
                $conn->send('hello world!');

            }, function(\Exception $e) use ($loop) {
                echo "Could not connect: {$e->getMessage()}\n";

                $loop->stop();
            });

        $loop->run();
    }
}
