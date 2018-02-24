<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\eventTrigger; // Linked the event

class TestLoop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testloop:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test loop console command for websocket output';

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
    public function handle() // Executable code
    {
        for($i = 0; $i < 50; $i++){
            usleep(500000); // 1000000 = 1 src
            event(new eventTrigger($i));
            echo $i;
        }

    }
}
