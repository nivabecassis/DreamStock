<?php

namespace App;

/**
 * Class used for querying worldtradingdata API
 * @package App
 */
class FinanceAPI
{
    /**
<<<<<<< HEAD
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
=======
>>>>>>> 790cb85de0f83746754b4a7e2cb6f80aefdb396b
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
        $strJson = file_get_contents(WORLDTRADINGDATA . $symbols . $apiKey);

        return json_decode($strJson);
    }
}