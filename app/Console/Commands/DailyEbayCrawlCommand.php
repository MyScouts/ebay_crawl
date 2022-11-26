<?php

namespace App\Console\Commands;

use App\Events\ExecCrawlEbayEvent;
use Illuminate\Console\Command;

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
        return event(new ExecCrawlEbayEvent());
    }
}
