<?php

if (!function_exists('activeClass')) {
    /**
     * Get the active class if the condition is not falsy.
     *
     * @param  $condition
     * @param  string  $activeClass
     * @param  string  $inactiveClass
     * @return string
     */
    function activeClass($condition, $activeClass = 'active', $inactiveClass = '')
    {
        return $condition ? $activeClass : $inactiveClass;
    }
}

if (!function_exists('htmlLang')) {
    /**
     * Access the htmlLang helper.
     */
    function htmlLang()
    {
        return str_replace('_', '-', app()->getLocale());
    }
}


if (!function_exists('highlightNumber')) {
    /**
     * Access the htmlLang helper.
     */
    function highlightNumber($text)
    {
        $text = strip_tags($text);
        return preg_replace("/([0-9]+)/", "<span class='text-danger font-weight-bold'>$1</span>", $text);
    }
}
