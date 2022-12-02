<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    const EBAY_CRAWL_URL = 'ebay-crawl-url';
    const EBAY_BASE_URL = 'ebay-base-url';
    const EBAY_DAILY_CRAWL = 'ebay-daily-crawl-times';
    const EXP_PUBLISH_MINUTES = 'exp-publish-minutes';

    protected $fillable = [
        'key',
        'value',
        'name'
    ];
}
