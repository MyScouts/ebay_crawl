<?php

namespace App\Console;

use App\Console\Commands\DailyEbayCrawlCommand;
use App\Console\Commands\ResetCachePublish;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Log;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $dailyTime = env('EBAY_DAILY_CRAWL');
        $times = explode(';', $dailyTime);
        foreach ($times as $time) {
            $schedule->command(DailyEbayCrawlCommand::class)->dailyAt($time);
        }

        $schedule->command(ResetCachePublish::class)->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
