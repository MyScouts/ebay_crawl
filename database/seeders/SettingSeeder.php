<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::insert([
            [
                'key' => Setting::EBAY_CRAWL_URL,
                'value' => 'https://www.ebay-kleinanzeigen.de/s-autos/anbieter:privat/anzeige:angebote/preis:200:5000/seite:__CURRENT_PAGE__/auto/k0c216',
                'name' => 'Ebay crawl url'
            ],
            [
                'key' => Setting::EBAY_BASE_URL,
                'value' => 'https://www.ebay-kleinanzeigen.de',
                'name' => 'Ebay base url'
            ],
            [
                'key' => Setting::EBAY_DAILY_CRAWL,
                'value' => '00:00;05:00;10:00;15:00;20:00',
                'name' => 'Ebay crawl with times'
            ],
            [
                'key' => Setting::EXP_PUBLISH_MINUTES,
                'value' => '60',
                'name' => 'Expiry publish minutes'
            ],
        ]);
    }
}
