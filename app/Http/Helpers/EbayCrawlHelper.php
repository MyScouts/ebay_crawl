<?php

namespace App\Http\Helpers;

use App\Domains\Auth\Models\User;
use App\Jobs\CrawlEbayJobs;
use App\Models\Product;
use App\Models\Setting;
use Bus;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use KubAT\PhpSimple\HtmlDomParser;
use Log;

class EbayCrawlHelper
{
    const EBAY_URL_KEY = 'EBAY_URL';

    /**
     * httpRequest
     *
     * @param  mixed $crawlUrls
     * @return
     */
    public static function httpRequest($crawlUrls)
    {
        $clientSetting = ['allow_redirects' => ['track_redirects' => true], 'verify' => false];

        $proxy = env('PROXY');
        $host = env('HOST');
        $useCustomProxy = env('CUSTOM_PROXY', false);
        if ($useCustomProxy) $clientSetting['proxy'] = "http://$proxy:$host";

        $client = new Client($clientSetting);
        $request = new Request('GET', $crawlUrls);
        $res = $client->sendAsync($request)->wait();
        return $res->getBody()->getContents();
    }

    /**
     * initUrl
     *
     * @return ?array
     */
    public static function getDetailUrls(int $page)
    {
        $crawlUrls = Setting::where('key', Setting::EBAY_CRAWL_URL)->select('value')->first();
        if (!isset($crawlUrls->value) || strlen($crawlUrls->value) <= 0) throw new Exception("Urls links for crawl not setting!");
        $crawlUrls = str_replace('__CURRENT_PAGE__', $page, $crawlUrls->value);
        $htmlContent = self::httpRequest($crawlUrls);
        $dom = HtmlDomParser::str_get_html($htmlContent);
        $cardElms = $dom->find('#srchrslt-adtable .ad-listitem');

        $urls = [];
        foreach ($cardElms as $value) {
            $timeElm = $value->find('.aditem-main .aditem-main--top--right');
            if (isset($timeElm) && count($timeElm) > 0) {
                $timeText = strip_tags($timeElm[0]->innertext);
                $time = trim(substr($timeText, strpos($timeText, ',') + 1));
                if (preg_match("/^(?:2[0-4]|[01][1-9]|10):([0-5][0-9])$/", $time) == 1) {
                    $dailyTimeSetting = Setting::where('key', Setting::EBAY_DAILY_CRAWL_PRODUCT_TIME)->select('value')->first();
                    $dailyTime = isset($dailyTimeSetting->value) && count(explode(';', $dailyTimeSetting->value)) == 2 ? $dailyTimeSetting->value : "03:00;16:00";
                    $times = explode(';', $dailyTime);
                    $time = strtotime($time);
                    $startDate = strtotime($times[0]);
                    $endDate = strtotime($times[1]);
                    if ($time >= $startDate && $time <= $endDate) {
                        $urlElm = $value->find('.aditem-image a');
                        if (isset($urlElm) && count($urlElm) > 0) $urls[] = $urlElm[0]->href;
                    }
                }
            }
        }

        $hasNextPageElms = $dom->find('#srchrslt-pagination .pagination-next');
        $hasNextPage = isset($hasNextPageElms) && count($hasNextPageElms) > 0;
        return [$urls, $hasNextPage];
    }

    /**
     * processingCrawl
     *
     * @param  mixed $crawlUrls
     * @return void
     */
    public static function processingCrawl(array $crawlUrls)
    {
        $ebayUrl = Setting::where('key', Setting::EBAY_BASE_URL)->select('value')->first();
        $ebayUrl = isset($ebayUrl->value) ? $ebayUrl->value : 'https://www.ebay-kleinanzeigen.de';
        $data = [];
        foreach ($crawlUrls as $key => $value) {
            $htmlDetail = self::httpRequest($ebayUrl . $value);
            $dom = HtmlDomParser::str_get_html($htmlDetail);

            $idElms = $dom->find('#viewad-ad-id-box ul li');
            $productId = is_array($idElms) && count($idElms) == 2 ? end($idElms)->innertext : null;

            $descriptionElm = $dom->find('#viewad-description #viewad-description-text');
            $description = is_array($descriptionElm) && count($descriptionElm) > 0 ? $descriptionElm[0]->innertext : null;
            $description = strip_tags(preg_replace("/<(?:br)[^>]*>/i", "\n", $description));
            $description = trim($description);

            if (!empty($productId) && !empty($description)) {
                $data[] = [
                    'ebay_id'       => $productId,
                    'description'   => $description,
                    'ebay_url'      => $value
                ];
            }
        }
        if (count($data) > 0) {
            foreach ($data as $item) {
                $product = Product::where('ebay_id', $item['ebay_id'])->get();
                if (!isset($product) || empty($product)) {
                    Log::info("EBAY-PRODUCT-SAVE", ['data' => $item]);
                    Product::create($item);
                }
            }
        };
    }

    /**
     * beginCrawl
     *
     * @return void
     */
    public static function beginCrawl()
    {
        Log::info("===========START-CRAWL===========");
        $page = 1;
        $next = true;
        $jobs = [];
        do {
            [$productUrls, $hasNext] = self::getDetailUrls($page);
            if (count($productUrls) > 0) {
                dispatch(new CrawlEbayJobs($productUrls));
            };
            Log::debug("STEP", ['page' => $page, 'totalUrl' => count($productUrls)]);
            $next = $hasNext;
            $page++;
        } while ($next);

        if (count($jobs) > 0) {
            // Bus::batch($jobs)->dispatch();
        }
    }
}
