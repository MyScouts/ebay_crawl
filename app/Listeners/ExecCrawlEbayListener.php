<?php

namespace App\Listeners;

use App\Events\ExecCrawlEbayEvent;
use App\Http\Helpers\EbayCrawlHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ExecCrawlEbayListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExecCrawlEbayEvent  $event
     * @return void
     */
    public function handle(ExecCrawlEbayEvent $event)
    {
        EbayCrawlHelper::beginCrawl();
    }
}
