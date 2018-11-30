<?php

namespace App;

use App\ApiUri;

class FinanceAPI 
{
    /**
     * Gets stock info as associative array
     *
     * @param $ticker Company to get stock information for
     * @return associative array
     */
    public static function getStockInfo($ticker)
    {
        $url = WORLDTRADINGDATA;
        $query = "?symbol=" . $ticker . "&api_token=" . APIKEY;

        return file_get_contents($url . $query);
    }
}