<?php

namespace App;

/**
 * Class used for querying worldtradingdata API
 * @package App
 */
class FinanceAPI
{
    /**
     * Gets stock info for all tickers
     *
     * @param $tickers Companies to get stock information for
     * @return json object
     */
    static function getAllStockInfo(array $tickers)
    {
        $symbols = "?symbol=";
        $apiKey = "&api_token=" . APIKEY;
        foreach ($tickers as $ticker) {
            $symbols .= $ticker . ",";
        }
        $symbols = substr($symbols, 0 , strlen($symbols ) - 1);
        return file_get_contents(WORLDTRADINGDATA . $symbols . $apiKey);
    }
}