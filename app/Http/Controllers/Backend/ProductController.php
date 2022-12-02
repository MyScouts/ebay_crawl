<?php

namespace App\Http\Controllers\Backend;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\PublishReqequest;
use App\Models\Product;
use App\Models\UserAction;
use Cache;
use Carbon\Carbon;
use Log;

class ProductController extends Controller
{

    public function index()
    {
        $publish = $this->getProductPublish();

        $cacheKey = str_replace('__USER_ID__', auth()->id(), Product::PRODUCT_PUBLISH_KEY);
        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;

        $productId = Cache::get($cacheKey);
        $productPublishing = Cache::get($productPublishingKey);
        $productIgnore = explode(',', $productPublishing);
        array_push($productIgnore, $productId);

        $total = Product::whereNotIn('id', $productIgnore)->whereNull('publish_date')->count();

        $expPublish = User::find(auth()->id());
        $hasExp =  !empty($expPublish->exp_publish) && Carbon::now()->lte($expPublish->exp_publish);
        $timeReload = $hasExp ? Carbon::now()->diffInSeconds($expPublish->exp_publish) * 1000 + 5000 : null;
        return view('backend.products.index', compact('publish', 'timeReload', 'total'));
    }

    public function publishDo(PublishReqequest $request, $productId)
    {
        UserAction::create([
            'user_id'       => auth()->id(),
            'action_type'   => UserAction::AC_SAVE
        ]);
        $product = Product::findOrFail($productId);
        $data = [
            'publish_date' => now(),
            'publisher'    => auth()->user()->email,
            'description'  => request('description')
        ];
        $this->updateProductStatus($productId, $data);
        return redirect()->route('admin.products.list')->withFlashSuccess("Product #{$product->ebay_id} is published!");
    }

    public function unPublishProduct($productId)
    {
        $product = Product::findOrFail($productId);
        UserAction::create([
            'user_id'       => auth()->id(),
            'action_type'   => UserAction::AC_DELETE
        ]);
        $data = [
            'deleted_at'    => now(),
            'publish_date'  => null
        ];
        $this->updateProductStatus($productId, $data);

        return redirect()->route('admin.products.list')->withFlashSuccess("Delete product #{$product->ebay_id} successfully!");
    }

    public function nextProduct()
    {
        UserAction::create([
            'user_id'       => auth()->id(),
            'action_type'   => UserAction::AC_NEXT
        ]);
        $cacheKey = str_replace('__USER_ID__', auth()->id(), Product::PRODUCT_PUBLISH_KEY);
        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;
        $productId = Cache::get($cacheKey);
        Cache::forget($cacheKey);

        if (Cache::has($productPublishingKey) && !empty($productId) && intval($productId) > 0) {
            $productsPublishing = explode(',', Cache::get($productPublishingKey));
            $pod = array_search($productId, $productsPublishing);
            if (count($productsPublishing) > 0 && isset($productsPublishing[$pod])) unset($productsPublishing[$pod]);
            $productsPublishing = array_unique(array_filter($productsPublishing));
            Cache::forever($productPublishingKey, implode(',', $productsPublishing));
        }
        return redirect()->route('admin.products.list');
    }

    private function getProductPublish()
    {
        $cacheKey = str_replace('__USER_ID__', auth()->id(), Product::PRODUCT_PUBLISH_KEY);
        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;
        $productsPublishing = [];
        $exitsProduct = false;

        if (Cache::has($productPublishingKey)) {
            $productsPublishing = explode(',', Cache::get($productPublishingKey));
        }

        $publish = Product::whereNull('publish_date')
            ->whereNotIn('id', $productsPublishing)
            ->select('ebay_id', 'ebay_url', 'description', 'id')
            ->get();

        if (!empty($publish) && count($publish) > 0) $publish = $publish->random(1);

        $publish = $publish->first();

        Log::info("getProductPublish", ['key' => $cacheKey, 'key' => Cache::get($cacheKey)]);
        if (Cache::has($cacheKey)) {
            $productId = Cache::get($cacheKey);
            $product = Product::where('id', $productId)
                ->whereNull('publish_date')
                ->select('ebay_id', 'ebay_url', 'description', 'id')
                ->first();

            $publish = empty($product) ? $publish : $product;
            $exitsProduct = true;
        }

        if (!empty($publish)) {
            Cache::forever($cacheKey, $publish->id);
            array_push($productsPublishing, $publish->id);
            $productsPublishing = array_unique(array_filter($productsPublishing));
            if (!$exitsProduct) $this->saveExpPublish(auth()->id());
        };

        Cache::forever($productPublishingKey, implode(',', $productsPublishing));
        return $publish;
    }

    private function updateProductStatus($productId, $data)
    {
        Product::where('id', $productId)->update($data);

        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;
        $productsPublishing = [];

        if (Cache::has($productPublishingKey)) {
            $productsPublishing = explode(',', Cache::get($productPublishingKey));
        }

        $pod = array_search($productId, $productsPublishing);
        if (count($productsPublishing) > 0 && isset($productsPublishing[$pod])) unset($productsPublishing[$pod]);

        $productsPublishing = array_unique(array_filter($productsPublishing));
        Cache::forever($productPublishingKey, implode(',', $productsPublishing));
    }


    /**
     * saveExpPublish
     *
     * @param  int $userId
     * @return void
     */
    private function saveExpPublish(int $userId): void
    {
        $expMinutes = config('product.exp_publish');
        User::where('id', $userId)->update([
            'exp_publish' => Carbon::now()->addMinutes($expMinutes)->format('Y-m-d H:i:00')
        ]);
    }
}
