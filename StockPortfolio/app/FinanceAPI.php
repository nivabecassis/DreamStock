<?php

namespace App;

use App\ApiUri;

class FinanceAPI 
{
    /**
     * Gets stock info as associative array
     *
     * @param $ticker Company to get stock information for
     * @return json object
     */
    public static function getStockInfo($ticker)
    {
        $query = "?symbol=" . $ticker . "&api_token=" . APIKEY;
        $strJson = file_get_contents(WORLDTRADINGDATA . $query);

        return json_decode($strJson);
    }


    /**
     * Gets stock info for all tickers
     *
     * @param $tickers Companies to get quotes from
     * @return json object
     */
    public static function getAllStockInfo(array $tickers)
    {
        $symbols = "?symbol=";
        $apiKey = "&api_token=" . APIKEY;
        foreach ($tickers as $ticker) {
            $symbols .= $ticker . ",";
        }
        $symbols = substr($symbols, 0 , strlen($symbols ) - 1);
        $strJson = file_get_contents(WORLDTRADINGDATA . $symbols . $apiKey);

        return json_decode($strJson);
    }
}

