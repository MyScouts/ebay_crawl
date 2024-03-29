<?php

namespace App\Http\Helpers;

use Log;
use Cache;
use Artisan;
use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UserAction;
use App\Jobs\CrawlEbayJobs;
use GuzzleHttp\Psr7\Request;
use App\Domains\Auth\Models\User;
use KubAT\PhpSimple\HtmlDomParser;

class EbayCrawlHelper
{
    const EBAY_URL_KEY          = 'EBAY_URL';
    const TOTAL_ERRORS_CRAWL    = "TOTAL_ERRORS_CRAWL";
    const TOTAL_ADD_PRODUCT     = "TOTAL_ADD_PRODUCT";

    /**
     * httpRequest
     *
     * @param  mixed $crawlUrls
     * @return
     */
    public static function httpRequest($crawlUrls)
    {
        $headers = [
            // 'authority'                 => 'suchen.mobile.de',
            'accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'accept-language'           => 'en-US,en;q=0.9,vi;q=0.8',
            // 'cache-control'             => 'max-age=0',
            // 'sec-ch-ua'                 => '"Google Chrome";v="107", "Chromium";v="107", "Not=A?Brand";v="24"',
            // 'sec-ch-ua-mobile'          => '?0',
            // 'sec-ch-ua-platform'        => '"macOS"',
            // 'sec-fetch-dest'            => 'document',
            // 'sec-fetch-mode'            => 'navigate',
            // 'sec-fetch-site'            => 'cross-site',
            // 'sec-fetch-user'            => '?1',
            // 'upgrade-insecure-requests' => '1',
            'user-agent'                => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            'Connection'                => 'keep-alive'
        ];

        $clientSetting = ['allow_redirects' => ['track_redirects' => true], 'verify' => false, 'timeout'  => 60];
        $client = new Client($clientSetting);
        $request = new Request('GET', $crawlUrls, $headers);
        $res = $client->sendAsync($request)->wait();
        Log::debug("ABDC", ['content' => $res]);
        return $res->getBody()->getContents();
    }
    public static function httpRequestSMS($Address)
    {
        $client = new Client(['verify' => false]);

        $headers = [
            'Authorization' => 'Bearer d24ab24a-62fc-4cd7-9a55-4c074389368a',
            'Content-Type' => 'application/json',
        ];

        $body = [
                "contentCategory" => "informational",
                "maxSmsPerMessage" => 10,
                "messageContent" => "Hallo, \nich habe deine Nummer von Kleinanzeigen. Falls du den Wert von deinem Auto genau wissen willst, kannst du das bei uns kostenlos auf www.fahrzeugbewertung.repareo.de machen. Dort bekommst du auch ein sofortiges Kaufangebot.\nLiebe Grüße \nLuka von repareo.de - größtes Werkstattportal Deutschlands",
                "messageType" => "default",
                "priority" => 5,
                "recipientAddressList" => [
                    $Address
                ],
                "senderAddress" => "repareo",
                "sendAsFlashSms" => false,
                "senderAddressType" => "alphanumeric",
                "test" => false,
                "validityPeriode" => 300
            ];
        $response = $client->post('https://api.linkmobility.eu/rest/smsmessaging/text', [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseData = $response->getBody()->getContents();
        Log::debug("SMS status", ['content' => $responseData]);
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

                    // if ($time >= $startDate && $time <= $endDate) {
                        $urlElm = $value->find('.aditem-image a');
                        if (isset($urlElm) && count($urlElm) > 0) $urls[] = $urlElm[0]->href;
                    // }
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
        Log::emergency("đã chạy");
        if (count($data) > 0) {
            foreach ($data as $item) {
                try {
                    $string = $item['description'];
                    $pattern = '/(?<!\d)(\+49|0|0049)[\d .\/:-]+(?!\d)/';
                    preg_match_all($pattern, $string, $matches);
                    $phone_numbers = array_map(function($num) {
                        return preg_replace('/[^0-9]/', '', $num);
                    }, $matches[0]); 
                    // $phone_numbers = array_map(function($num) {
                    //     return preg_replace('/490/', '49', $num);
                    // }, $matches[0]); 
                    
                    $phone_numbers = array_filter($phone_numbers, function($num) {
                        $num = substr($num, -14);
                        return strlen($num) >= 9 && strlen($num) <= 14;  
                    });
                    
                    if(!isset($phone_numbers[0]))
                    {
                        
                    }
                    else {
                        $mytime = Carbon::now();
                        Log::alert($phone_numbers[0]);
                        $phone_numbers_cover = $phone_numbers[0];
                        if (substr($phone_numbers_cover, 0, 3) === "490") {
                            $phone_numbers_cover = "+49" . substr($phone_numbers_cover, 3);
                        }
                        else
                        {
                            $phone_numbers_cover = preg_replace('/^(00|\+)?(49|490|0)?([0-9]+)/', '+49$3', $phone_numbers[0]);
                        }
                        
                        Log::alert("chuyển đổi 1 : ".$phone_numbers_cover);


                        
                        Log::alert("chuyển đổi : ".$phone_numbers_cover);
                        $ProductCheck =  Product::where("description",str_replace(" ","",$phone_numbers_cover))->first();
                        if($ProductCheck == null)
                        {
                            Log::alert("Chưa có thông tin này gửi tin nhắn");
                            self::httpRequestSMS($phone_numbers_cover);
                            UserAction::create([
                                'user_id'           => User::first()->id,
                                'action_type'       => 1,
                            ]);
                            Product::create([
                                'ebay_id'       => $item['ebay_id'],
                                'description'   => str_replace(" ","",$phone_numbers_cover),
                                'ebay_url'      => $item['ebay_url'],
                                'publish_date'  => $mytime->toDateTimeString(),
                                'publisher'     => User::first()->email
                            ]);                            
                        }
                        else
                        {
                            $Product =  Product::where("description",str_replace(" ","",$phone_numbers_cover))->first();
                            Log::alert("có rồi");
                            $time1 = Carbon::parse($Product->publish_date); 
                            $time2 = Carbon::parse($mytime->toDateTimeString());

                            if ($time2->diffInDays($time1) > 7) {
                                // $time2 lớn hơn $time1 7 ngày
                                Log::alert("Thấy sau 7 ngày gửi tin nhắn");
                                $Product->update(['publish_date'=>$mytime->toDateTimeString()]);
                                self::httpRequestSMS($phone_numbers_cover);
                                
                            } else {
                                // $time2 không lớn hơn $time1 7 ngày
                                Log::alert("Đã thấy trong 7 ngày gần đây");

                            }
                        }

                    
                    }

                    Cache::increment(self::TOTAL_ADD_PRODUCT, 1);
                    
                } catch (\Throwable $th) {
                    Log::emergency("có lỗi xảy ra".$th);
                    Cache::increment(self::TOTAL_ERRORS_CRAWL, 1);
                    $totalErrors = Cache::get(self::TOTAL_ERRORS_CRAWL);
                    $totalErrors = intval($totalErrors);
                    if ($totalErrors >= 150) {
                        Artisan::call('queue:clear');
                        Log::alert("CANCEL JOB", ['TOTAL-PRODUCT-ADDED' => Cache::get(self::TOTAL_ADD_PRODUCT)]);
                        return;
                    }
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
        Cache::forget(self::TOTAL_ERRORS_CRAWL);
        Cache::forget(self::TOTAL_ADD_PRODUCT);
        $page = 1;
        $next = true;
        $jobs = [];
        do {
            [$productUrls, $hasNext] = self::getDetailUrls($page);
            if (count($productUrls) > 0) {
                dispatch(new CrawlEbayJobs($productUrls));
            };
            $next = $hasNext;
            $page++;
        } while ($next);

        if (count($jobs) > 0) {
            // Bus::batch($jobs)->dispatch();
        }
    }
}
