<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\FinanceAPI;
use App\UserUtility;

/*
 * PortfolioController is a controller class which performs 'Read' action against our database.
 * It retrieves the information, manipulates it and returns:
 * 1) User's balance
 * 2) User's total portfolio value
 * 3) User's total last daily close portfolio value
 * 4) User's share count of each individual company
 */
class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Gets user balance
     *
     * @param json Json object of authenticated user's metadata 
     * @return balance User's balance
     */
    public function getBalance($json)
    {
        return $json->portfolios->cash_owned;
    }

    /**
     * Gets total sum of all current portfolio value
     * 
     * @param array Array of stock information
     * @param array Array of ticker symbols
     * @return totalCurrentValue Sum of all current portfolio value
     */
    public function getPortfolioValue($data, $tickers)
    {
        // Gets all user's current total price for each company
        $prices = $this->getAllPrices($data, $tickers);

        // Returns sum of all prices
        return $this->sumAll($prices);
    }

    /**
     * Gets total sum of all last close price portfolio value
     * 
     * @param array Array of stock information
     * @param array Array of ticker symbols
     * @return totalLastCloseValue Sum of all last close portfolio value
     */
    public function getPortfolioLastCloseValue($data, $tickers)
    {
        // Gets all user's last daily close price for each company
        $prices = $this->getAllLastClosePrices($data, $tickers);

        // Returns sum of all prices
        return $this->sumAll($prices);
    }

    /**
     * Loads authenticated user's metadata
     * 
     * @return array Array which contains stock information and tickers 
     */
    private function loadMetadata($user)
    {
        // Gets authenticated user's metadatas
        $user->portfolios->portfolio_stocks;

        // Loads all metadata
        $stocksData = $user->portfolios->portfolio_stocks;

        // Gets all user's share counts
        $tickers = $this->getShareCount($stocksData);

        // Returns stock information through API request and tickers
        return array(
            'info' => FinanceAPI::getAllStockInfo(array_keys($tickers)),
            'tickers' => $tickers
        );
    }

    /**
     * Stores share count of each company that the authenticated user bought
     * 
     * @param metadata User's metadata
     * @return array Array of share count
     */
    private function getShareCount($metadata)
    {
        $shareCount = array();
        foreach ($metadata as $value) {
            $shareCount[$value->ticker_symbol] = $value->share_count;
        }

        return $shareCount;
    }

    /**
     * Current portfolio value
     * Stores total price of each company in an array
     * 
     * @param array Assoc array containing stock information from API
     * @param array Array of share counts
     * @return array Array of total price
     */
    private function getAllPrices($data, array $tickers)
    {
        $currentPrices = array();
        foreach ($data as $value) { // Loop through array 
            foreach ($tickers as $key => $share) { // Loop through all user's share count
                if ($value['symbol'] === $key) { // Check if symbols are matching
                    array_push($currentPrices, $value['price'] * $share);
                }
            }
        }

        return $currentPrices;
    }

    /**
     * Last daily close portfolio value
     * Stores total price of each company in an array
     * 
     * @param array Assoc array containing stock information from API
     * @param array Array of share counts
     * @return array Array of total price
     */
    private function getAllLastClosePrices($data, array $tickers)
    {
        $lastClosePrice = array();
        foreach ($data as $value) { // Loop through array 
            foreach ($tickers as $key => $share) { // Loop through all user's share count
                if ($value['symbol'] === $key) { // Check if symbols are matching
                    array_push($lastClosePrice, $value['close_yesterday'] * $share);
                }
            }
        }

        return $lastClosePrice;
    }

    /**
     * Sums all prices
     * 
     * @param array Array containing every prices
     * @return decimal Sum of all prices
     */
    private function sumAll(array $prices)
    {
        $sum = 0;
        foreach ($prices as $value) {
            $sum += $value;
        }

        return $sum;
    }
}
