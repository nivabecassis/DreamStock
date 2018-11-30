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
    function getStockInfo($ticker)
    {
        $query = "?symbol=" . $ticker . "&api_token=" . APIKEY;
        return file_get_contents(WORLDTRADINGDATA . $query);
    }


    /**
     * Gets stock info for all tickers
     *
     * @param $tickers Companies to get quotes from
     * @return json object
     */
    function getGlobalQuote(array $tickers)
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

