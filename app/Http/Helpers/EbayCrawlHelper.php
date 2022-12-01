<?php

namespace App\Http\Helpers;

use App\Domains\Auth\Models\User;
use App\Jobs\CrawlEbayJobs;
use App\Models\Product;
use Bus;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use KubAT\PhpSimple\HtmlDomParser;
use Log;

class EbayCrawlHelper
{
    const CRAWL_URL_KEY = 'EBAY_CRAWL_URL';
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
        Log::info('EbayCrawlHelper ::: httpRequest', ['url' => $crawlUrls, 'statusCode' => $res->getStatusCode()]);
        return $res->getBody()->getContents();
    }

    /**
     * initUrl
     *
     * @return ?array
     */
    public static function getDetailUrls(int $page)
    {
        $crawlUrls = env(EbayCrawlHelper::CRAWL_URL_KEY);
        if (strlen($crawlUrls) <= 0) throw new Exception("Urls links for crawl not setting!");
        $crawlUrls = str_replace('__CURRENT_PAGE__', $page, $crawlUrls);

        $htmlContent = self::httpRequest($crawlUrls);
        $dom = HtmlDomParser::str_get_html($htmlContent);
        $cardElms = $dom->find('#srchrslt-adtable .ad-listitem a');

        $hasNextPageElms = $dom->find('#srchrslt-pagination .pagination-next');

        $urls = [];
        foreach ($cardElms as $key => $value) {
            $urls[] = $value->href;
        }
        $hasNextPage =  isset($hasNextPageElms) && count($hasNextPageElms) > 0;
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
        $ebayUrl = env(self::EBAY_URL_KEY, 'https://www.ebay-kleinanzeigen.de');
        $data = [];
        foreach ($crawlUrls as $key => $value) {
            $htmlDetail = self::httpRequest($ebayUrl . $value);
            $dom = HtmlDomParser::str_get_html($htmlDetail);

            $idElms = $dom->find('#viewad-ad-id-box ul li');
            $productId = is_array($idElms) && count($idElms) == 2 ? end($idElms)->innertext : null;

            $descriptionElm = $dom->find('#viewad-description #viewad-description-text');
            $description = is_array($descriptionElm) && count($descriptionElm) > 0 ? $descriptionElm[0]->innertext : null;
            $description = preg_replace("/<(?:br)[^>]*>/i", "\n", $description);
            $description = trim($description);

            if (!empty($productId) && !empty($description)) {
                $data[] = [
                    'ebay_id'       => $productId,
                    'description'   => $description,
                    'ebay_url'      => $value
                ];
            }
        }
        if (count($data) > 0) Product::upsert($data, ['ebay_id'], ['description', 'ebay_url']);
    }

    /**
     * beginCrawl
     *
     * @return void
     */
    public static function beginCrawl()
    {
        $page = 1;
        $next = true;
        $jobs = [];
        do {
            [$productUrls, $hasNext] = self::getDetailUrls($page);
            Log::info("EbayCrawlHelper:: beginCrawl", ['url' => count($productUrls), 'next' => $hasNext, 'page' => $page]);
            // self::processingCrawl($productUrls);
            if (count($productUrls) > 0) dispatch(new CrawlEbayJobs($productUrls));
            $next = $hasNext;
            $page++;
        } while ($next);

        if (count($jobs) > 0) {
            // Bus::batch($jobs)->dispatch();
        }
    }
}
