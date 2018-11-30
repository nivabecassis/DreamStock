<?php

namespace App;

use App\ApiUri;

/**
 * Gets stock info as associative array
 *
 * @param $ticker Company to get stock information for
 * @return associative array
 */
if (!function_exists('getStockInfo')) { // Checks if function is already defined
    function getStockInfo($ticker)
    {
        $url = WORLDTRADINGDATA;
        $query = "?symbol=" . $ticker . "& api_token=" . APIKEY;
        return json_decode(file_get_contents($url . $query));
    }
}
