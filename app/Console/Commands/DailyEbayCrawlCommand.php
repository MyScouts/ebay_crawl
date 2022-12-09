<?php

namespace App\Console\Commands;

use App\Events\ExecCrawlEbayEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyEbayCrawlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:daily-ebay-crawl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        Log::alert("========START_DAILY_CRAWL========");
        return event(new ExecCrawlEbayEvent());
    }
}
