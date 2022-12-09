<?php

namespace App\Console\Commands;

use App\Domains\Auth\Models\User;
use App\Models\Product;
use Cache;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Log;
use PhpParser\Builder\Use_;

class ResetCachePublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:reset-cache-publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'reset all cache user publish product';

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
        $userIds = User::whereNotNull('exp_publish')
            ->where('exp_publish', '<=', Carbon::now())
            ->select('id')
            ->pluck('id')
            ->toArray();

        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;
        $productsPublishing = [];
        if (Cache::has($productPublishingKey)) {
            $productsPublishing = explode(',', Cache::get($productPublishingKey));
        }

        if (count($productsPublishing) <= 0) return;

        foreach ($userIds as $key => $userId) {
            $cacheKey = str_replace('__USER_ID__', $userId, Product::PRODUCT_PUBLISH_KEY);
            if (Cache::has($cacheKey)) {
                $productId = Cache::get($cacheKey);
                Cache::forget($cacheKey);
                $pod = array_search($productId, $productsPublishing);
                if (isset($productsPublishing[$pod])) unset($productsPublishing[$pod]);
            }
        }

        $productsPublishing = array_unique(array_filter($productsPublishing));
        Cache::forever($productPublishingKey, implode(',', $productsPublishing));
        User::whereIn('id', $userIds)->update(['exp_publish' => null]);
    }
}
