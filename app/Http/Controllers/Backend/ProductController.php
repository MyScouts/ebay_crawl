<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Cache;

class ProductController extends Controller
{

    public function index()
    {
        $publish = $this->getProductPublish();
        return view('backend.products.index', compact('publish'));
    }

    public function publishDo($productId)
    {
        $product = Product::findOrFail($productId);
        $data = [
            'publish_date' => now(),
            'publisher'    => auth()->user()->email,
        ];
        $this->updateProductStatus($productId, $data);
        return redirect()->route('admin.products.list')->withFlashSuccess("Product #{$product->ebay_id} is published!");
    }

    public function unPublishProduct($productId)
    {

        $product = Product::findOrFail($productId);
        $data = [
            'deleted_at'    => now(),
            'publish_date'  => null
        ];
        $this->updateProductStatus($productId, $data);

        return redirect()->route('admin.products.list')->withFlashSuccess("UnPublish product #{$product->ebay_id} successfully!");
    }

    public function nextProduct()
    {
        $cacheKey = str_replace('__USER_ID__', auth()->id(), Product::PRODUCT_PUBLISH_KEY);
        Cache::forget($cacheKey);
        return redirect()->route('admin.products.list');
    }

    private function getProductPublish()
    {
        $cacheKey = str_replace('__USER_ID__', auth()->id(), Product::PRODUCT_PUBLISH_KEY);
        $productPublishingKey = Product::PRODUCT_PUBLISHING_KEY;
        $productsPublishing = [];

        if (Cache::has($productPublishingKey)) {
            $productsPublishing = explode(',', Cache::get($productPublishingKey));
        }

        $publish = Product::whereNull('publish_date')
            ->whereNotIn('id', $productsPublishing)
            ->select('ebay_id', 'ebay_url', 'description', 'id')
            ->first();
        if (Cache::has($cacheKey)) {
            $productId = Cache::get($cacheKey);
            $product = Product::where('id', $productId)
                ->whereNull('publish_date')
                ->select('ebay_id', 'ebay_url', 'description', 'id')
                ->first();
            $publish = $product ?? $publish;
        }

        if (!empty($publish)) {
            Cache::forever($cacheKey, $publish->id);
            array_push($productsPublishing, $publish->id);
            $productsPublishing = array_unique(array_filter($productsPublishing));
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
}
