<?php

namespace App;

/**
 * Class used for querying worldtradingdata API
 * @package App
 */
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
        $url = WORLDTRADINGDATA;
        $query = "?symbol=" . $ticker . "&api_token=" . APIKEY;
        return file_get_contents($url . $query);
    }


    /**
     * Gets array of stocks will be used to display quotes
     *
     * @param $tickers Companies to get quotes from
     * @return $stocks an array of associative arrays
     */
    function getGlobalQuote(array $tickers)
    {
        $stocks = array();
        foreach ($tickers as $ticker)
        {
            array_push($stocks, getStockInfo($ticker));
        }

        return $stocks;
    }
}