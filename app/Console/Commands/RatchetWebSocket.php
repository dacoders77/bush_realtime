<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\eventTrigger; // Linked events
use Illuminate\Support\Facades\DB;

class RatchetWebSocket extends Command
{
    public $symbol; // Trading symbol
    public $timeFrame;

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

        $timeframe =
            DB::table('settings')
                ->where('id', 1)
                ->value('time_frame');

        // Get traded symbol from DB. String must look like: tBTCUSD
        // MAKE IT UPPER CASE!
        $this->symbol = "t" .
            DB::table('settings')
                ->where('id', 1)
                ->value('symbol');

        //$this->symbol = "tBTCUSD";
        //dd ($this->symbol);

        // Code from: https://github.com/ratchetphp/Pawl
        $loop = \React\EventLoop\Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, [
            'dns' => '8.8.8.8', // Does not work through OKADO inernet provider. Timeout error
            'timeout' => 10
        ]);

        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);

        $connector('wss://api.bitfinex.com/ws/2', [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) {
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn) {

                    RatchetWebSocket::out($msg); // Call the function when the event is received
                    //echo $msg;

                });

                $conn->on('close', function($code = null, $reason = null) {
                    echo "Connection closed ({$code} - {$reason})\n";
                    $this->error("line 67. connection closed");
                    $this->error("Reconnecting back!");
                    $this->handle();

                });

                //$conn->send(['event' => 'ping']);
                $z = json_encode([
                    //'event' => 'ping', // 'event' => 'ping'
                    'event' => 'subscribe',
                    'channel' => 'trades',
                    'symbol' => $this->symbol // tBTCUSD tETHUSD tETHBTC
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


    public $dateCompeareFlag = true;
    public $tt; // Time

    public $barHigh = 0; // For high value calculation
    public $barLow = 9999999;

    public $trade_flag = "all";
    public $add_bar_long = true; // Count closed position on the same be the signal occurred. The problem is when the position is closed the close price of this bar goes to the next position
    public $add_bar_short = true;
    public $position; // Current position
    public $volume = "0.025"; // Asset amount for order opening
    public $firstPositionEver = true; // Skip the first trade record. When it occurs we ignore calculations and make accumulated_profit = 0. On the next step (next bar) there will be the link to this value
    public $firstEverTradeFlag = true; // True - when the bot is started and the first trade is executed. Then flag turns to false and trade volume is doubled for closing current position and opening the opposite


    public function out($message)
    {


        $jsonMessage = json_decode($message->getPayload(), true); // Methods http://socketo.me/api/class-Ratchet.RFC6455.Messaging.MessageInterface.html
        //print_r($jsonMessage);
        //print_r(array_keys($z));
        //echo $message->__toString() . "\n"; // Decode each message

        if (array_key_exists('chanId',$jsonMessage)){
            $chanId = $jsonMessage['chanId']; // Parsed channel ID then we are gonna listen exactley to this channel number. It changes each time you make a new connection
        }

        $nojsonMessage = json_decode($message->getPayload());
        if (!array_key_exists('event',$jsonMessage)){ // All messages except first two associated arrays
            if ($nojsonMessage[1] == "te") // Only for the messages with 'te' flag. The faster ones
            {
                //echo "id: " . $nojsonMessage[2][0];
                //echo " date: " . gmdate("Y-m-d G:i:s", ($nojsonMessage[2][1] / 1000));
                //echo " volume: " . $nojsonMessage[2][2];
                //echo " price: " . $nojsonMessage[2][3] . "\n";

                // current trade(tick): $nojsonMessage[2][3]
                // volume: $nojsonMessage[2][2]

                // Take seconds off and add 1 min. Do it only once per interval (for example 1min)
                if ($this->dateCompeareFlag) {
                    $x = date("Y-m-d H:i", $nojsonMessage[2][1] / 1000) . "\n"; // Take seconds off. Convert timestamp to date
                    $this->tt = strtotime($x . $this->timeFrame . 'minute'); // Timeframe. Added 1 minute. Timestamp
                    $this->dateCompeareFlag = false;
                }

                // Make a signal when value reaches over added 1 minute
                echo "Ticker: " . $this->symbol . " time: " . gmdate("Y-m-d G:i:s", ($nojsonMessage[2][1] / 1000)) ." price: " . $nojsonMessage[2][3] . " vol: " . $nojsonMessage[2][2] . " pos: " . $this->position . "\n";


                // Calculate high and low of the bar then pass it to the chart in $messageArray
                if ($nojsonMessage[2][3] > $this->barHigh) // High
                {
                    $this->barHigh = $nojsonMessage[2][3];
                }

                if ($nojsonMessage[2][3] < $this->barLow) // Low
                {
                    $this->barLow = $nojsonMessage[2][3];
                }

                // RATCHET ERROR GOES HERE, WHILE INITIAL START FROM GIU. trying to property of non-object
                // Update high, low and close of the current bar in DB. Update the record on each trade.
                // Then the new bar will be issued - we will have actual values updated in the DB

                // ERROR: Trying to property of non object
                // Occures when ratchet:start is run for the first time and the history table is empty - no record to update
                // Start GIU first and then rathcet:start
                // Updating last bar in the table. At the first run the table is not empty. Historical bars were loaded
                try {
                    DB::table('btc_history')
                        ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // id of the last record. desc - descent order
                        ->update([
                            'close' => $nojsonMessage[2][3],
                            'high' => $this->barHigh,
                            'low' => $this->barLow,
                        ]);
                }
                catch(Exception $e) {
                    echo 'Error when truncate the table on initial start: ' .$e->getMessage();
                }




                // NEW BAR IS ISSUED
                if (floor(($nojsonMessage[2][1] / 1000)) >= $this->tt){

                    // Experiment
                    // Add new bar to the DB
                    DB::table('btc_history')->insert(array( // Record to DB
                        'date' => gmdate("Y-m-d G:i:s", ($nojsonMessage[2][1] / 1000)), // Date in regular format. Converted from unix timestamp
                        'time_stamp' => $nojsonMessage[2][1],
                        'open' => $nojsonMessage[2][3],
                        'close' => $nojsonMessage[2][3],
                        'high' => $nojsonMessage[2][3],
                        'low' => $nojsonMessage[2][3],
                        'volume' => $nojsonMessage[2][2],
                    ));

                    // Get the price of the last trade
                    $lastTradePrice = // Last trade price
                        DB::table('btc_history')
                            ->whereNotNull('trade_price') // not null trade price value
                            ->orderBy('id', 'desc') // form biggest to smallest values
                            ->value('trade_price'); // get trade price value



                    // Calculate trade profit
                    $tradeProfit = ($this->position != null ? (($this->position == "long" ? ($nojsonMessage[2][3] - $lastTradePrice) * $this->volume : ($lastTradePrice - $nojsonMessage[2][3]) * $this->volume)) : false); // Calculate trade profit only if the position is open. Because we reach this code all the time when high or low price channel boundary is exceeded

                    if ($this->position != null){ // Do not calculate progit if there is not open position. If do not do this check - zeros in table occurs
                        DB::table('btc_history')
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id)
                            ->update([
                                // Calculate trade profit only if the position is open. Because we reach this code all the time when high or low price channel boundary is exceeded
                                'trade_profit' => $tradeProfit,
                            ]);
                    }


                    $this->line("\n************************************** new bar issued");
                    $messageArray['flag'] = true; // Added true flag which will inform JS that new bar is issued
                    $this->dateCompeareFlag = true;



                    // Trades watch
                    // Quantity of all records in DB
                    $x = (DB::table('btc_history')->orderBy('time_stamp', 'desc')->get())[0]->id;

                    // Get price
                    // Channel value of previous (penultimate bar)
                    $price_channel_high_value =
                        DB::table('btc_history')
                            ->where('id', ($x - 1)) // Penultimate record. One before last
                            ->value('price_channel_high_value');

                    $price_channel_low_value =
                        DB::table('btc_history')
                            ->where('id', ($x - 1)) // Penultimate record. One before last
                            ->value('price_channel_low_value');

                    $allow_trading =
                        DB::table('settings')
                            ->where('id', 1)
                            ->value('allow_trading');

                    $commisionValue =
                        DB::table('settings')
                            ->where('id', 1)
                            ->value('commission_value');


                    // If > high price channel. BUY
                    // price > price channel
                    if (($nojsonMessage[2][3] > $price_channel_high_value) && ($this->trade_flag == "all" || $this->trade_flag == "long")){
                        echo "####### HIGH TRADE!\n";

                        // trading allowed?
                        if ($allow_trading == 1){

                            // Is the the first trade ever?
                            if ($this->firstEverTradeFlag){
                                // open order buy vol = vol
                                echo "---------------------- FIRST EVER TRADE\n";
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"buy");
                                $this->firstEverTradeFlag = false;
                            }
                            else // Not the first trade. Close the current position and open opposite trade. vol = vol * 2
                            {
                                // open order buy vol = vol * 2
                                echo "---------------------- NOT FIRST EVER TRADE. CLOSE + OPEN. VOL*2\n";
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"buy");
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"buy");
                            }
                        }
                        else{ // trading is not allowed
                            $this->firstEverTradeFlag = true;
                            echo "---------------------- TRADING NOT ALLOWED\n";
                        }



                        $this->trade_flag = "short"; // Trade flag. If this flag set to short -> don't enter this if and wait for channel low crossing (IF below)
                        $this->position = "long";
                        $this->add_bar_long = true;


                        // Add(update) trade info to the last(current) bar(record)
                        DB::table('btc_history')
                            ->where('id', $x)
                            ->update([
                                'trade_date' => gmdate("Y-m-d G:i:s", ($nojsonMessage[2][1] / 1000)),
                                'trade_price' => $nojsonMessage[2][3],
                                'trade_direction' => "buy",
                                'trade_volume' => $this->volume,
                                'trade_commission' => ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume,
                                'accumulated_commission' => DB::table('btc_history')->sum('trade_commission') + ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume,
                            ]);

                        echo "nojsonMessage[2][3]" . $nojsonMessage[2][3] . "\n";
                        echo "commisionValue" . $commisionValue . "\n";
                        echo "this colume" . $this->volume . "\n";
                        echo "percent: " . ($nojsonMessage[2][3] * $commisionValue / 100) . "\n";
                        echo "result: " . ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume . "\n";
                        echo "sum: " . DB::table('btc_history')->sum('trade_commission') . "\n";

                        $messageArray['flag'] = "buy"; // Send flag to VueJS app.js. On this event VueJS is informed that the trade occurred

                    } // BUY trade





                    // If < low price channel. SELL
                    if (($nojsonMessage[2][3] < $price_channel_low_value) && ($this->trade_flag == "all"  || $this->trade_flag == "short")) { // price < price channel
                        echo "####### LOW TRADE!\n";

                        // trading allowed?
                        if ($allow_trading == 1){

                            // Is the the first trade ever?
                            if ($this->firstEverTradeFlag){
                                // open order buy vol = vol
                                echo "---------------------- FIRST EVER TRADE\n";
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"sell");
                                $this->firstEverTradeFlag = false;
                            }
                            else // Not the first trade. Close the current position and open opposite trade. vol = vol * 2
                            {
                                // open order buy vol = vol * 2
                                echo "---------------------- NOT FIRST EVER TRADE. CLOSE + OPEN. VOL*2\n";
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"sell");
                                app('App\Http\Controllers\PlaceOrder')->index($this->volume,"sell");
                            }
                        }
                        else{ // trading is not allowed
                            $this->firstEverTradeFlag = true;
                            echo "---------------------- TRADING NOT ALLOWED\n";
                        }

                        $this->trade_flag = "long";
                        $this->position = "short";
                        $this->add_bar_short = true;


                        // Add(update) trade info to the last(current) bar(record)
                        // EXCLUDE THIS CODE TO SEPARATE CLASS!!!!!!!!!!!!!!!!!!!
                        DB::table('btc_history')
                            ->where('id', $x)
                            ->update([
                                'trade_date' => gmdate("Y-m-d G:i:s", ($nojsonMessage[2][1] / 1000)),
                                'trade_price' => $nojsonMessage[2][3],
                                'trade_direction' => "sell",
                                'trade_volume' => $this->volume,
                                'trade_commission' => ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume,
                                'accumulated_commission' => DB::table('btc_history')->sum('trade_commission') + ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume,
                            ]);

                        //echo "nojsonMessage[2][3]" . $nojsonMessage[2][3] . "\n";
                        //echo "commisionValue" . $commisionValue . "\n";
                        //echo "this colume" . $this->volume . "\n";
                        //echo "percent: " . ($nojsonMessage[2][3] * $commisionValue / 100) . "\n";
                        //echo "result: " . ($nojsonMessage[2][3] * $commisionValue / 100) * $this->volume . "\n";
                        //echo "sum: " . DB::table('btc_history')->sum('trade_commission') . "\n";

                        $messageArray['flag'] = "sell"; // Send flag to VueJS app.js

                    } // Sell trade




                    // ****RECALCULATED ACCUMULATED PROFIT****
                    // Get the the if of last row where trade direction is not null

                    // if trade direction == null
                    $tradeDirection =
                        DB::table('btc_history')
                            ->where('id', (DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id))
                            ->value('trade_direction');

                    if ($tradeDirection == null && $this->position != null){

                        $lastAccumProfitValue =
                            DB::table('btc_history')
                                ->whereNotNull('trade_direction')
                                ->orderBy('id', 'desc')
                                ->value('accumulated_profit');
                        DB::table('btc_history')
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // id of the last record. desc - descent order
                            ->update([
                                'accumulated_profit' => $lastAccumProfitValue + $tradeProfit
                                //'accumulated_profit' => 789789
                            ]);

                        $this->error("Bar with no trade");
                        $this->info("lastAccumProfitValue: " . $lastAccumProfitValue . " tradeProfit: ". $tradeProfit);
                        //die();
                    }

                    if ($tradeDirection != null && $this->firstPositionEver == false) // Means that at this bar trade has occurred
                    {

                        //transition::orderBy('created_at', 'desc')->skip(1)->take(1)->get();

                        $nextToLastDirection =
                            DB::table('btc_history')
                                ->whereNotNull('trade_direction')
                                ->orderBy('id', 'desc')->skip(1)->take(1) // Second to last (penultimate). ->get()
                                ->value('accumulated_profit');


                        DB::table('btc_history')
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // id of the last record. desc - descent order
                            ->update([
                                'accumulated_profit' => $nextToLastDirection + $tradeProfit
                            ]);

                        $this->error("Bar with trade. nextToLastDirection: " . $nextToLastDirection);
                    }

                    // 1. Skip the first trade. Record 0 to accumulated_profit cell. This code fires once only at the first trade
                    if ($tradeDirection != null && $this->firstPositionEver == true){

                        DB::table('btc_history')
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // id of the last record. desc - descent order
                            ->update([
                                'accumulated_profit' => 0
                            ]);

                        $this->error("firstPositionEver!");
                        $this->firstPositionEver = false;

                    }











                    // NET PROFIT net_profit
                    if ($this->position != null){

                        $accumulatedProfit =
                            DB::table('btc_history')
                                ->where('id', (DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id))
                                ->value('accumulated_profit');

                        $accumulatedCommission =
                            DB::table('btc_history')
                                ->where('id', (DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id))
                                ->value('accumulated_commission');

                        DB::table('btc_history')
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // Quantity of all records in DB
                            ->update([
                                'net_profit' => $accumulatedProfit - $accumulatedCommission
                            ]);

                    }



                    // NET PROFIT net_profit_test ******************************************** IS RECORDED TO A SEPARATE COLUMN
                    // accum_profit - last accum commission
                    $netProfitTest =
                        DB::table('btc_history') // Current value of trade profit
                            ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id)
                            ->value('accumulated_profit') -
                        DB::table('btc_history')->sum('trade_commission'); // Trade commission column sum

                    DB::table('btc_history')
                        ->where('id', DB::table('btc_history')->orderBy('time_stamp', 'desc')->first()->id) // Quantity of all records in DB
                        ->update([
                            'net_profit_test' => $netProfitTest
                        ]);

                    $this->comment("netProfitTest:" . $netProfitTest);






                    // Recalculate price channel. Controller call as a method
                    app('App\Http\Controllers\indicatorPriceChannel')->index();

                } // New bar is issued

                // Add calculated values to associative array
                $messageArray['tradeId'] = $nojsonMessage[2][0]; // $messageArray['flag'] = true; And all these values will be sent to VueJS
                $messageArray['tradeDate'] = $nojsonMessage[2][1];
                $messageArray['tradeVolume'] = $nojsonMessage[2][2];
                $messageArray['tradePrice'] = $nojsonMessage[2][3];
                $messageArray['tradeBarHigh'] = $this->barHigh; // Bar high
                $messageArray['tradeBarLow'] = $this->barLow; // Bar Low

                // Send filled associated array in the event
                event(new eventTrigger($messageArray)); // Fire new event. Events are located in app/Events

                // Reset high, low of the bar but do not out put these values to the chart
                if ($this->dateCompeareFlag == true){
                    $this->barHigh = 0;
                    $this->barLow = 9999999;
                }

            } // if

        } // if

    } // out
}
