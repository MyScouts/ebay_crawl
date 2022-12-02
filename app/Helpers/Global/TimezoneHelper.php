<?php

use Carbon\Carbon;
use JamesMills\LaravelTimezone\Timezone;

if (! function_exists('timezone')) {
    /**
     * Access the timezone helper.
     */
    function timezone()
    {
        return resolve(Timezone::class);
    }
}

if (!function_exists('dateFormat')) {
    /**
     * Access the dateFormat helper.
     */
    function dateFormat($date, $in = 'd/m/Y', $out = 'Y-m-d')
    {
        return Carbon::createFromFormat($in, $date)->format($out);
    }
}
