<?php

namespace App\Console\Commands;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:clean-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean product data';

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
        Product::where('deleted_at', '<=', Carbon::now()->subDays(1))->forceDelete();
        Product::where('created_at', '<=', Carbon::now()->subDays(3))->whereNotNull('publish_date')->forceDelete();
    }
}
