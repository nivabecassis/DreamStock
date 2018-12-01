<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\FinanceAPI;

/*
 * Portfolio controller
 *
 * @author Austin Antoine
 * @author Stephen Kwan
 */
class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Gets current authenticated user
        $user = Auth::user();

        // Loads metadata
        $json = $this->loadMetadata($user);
        $data = $json['info']['data'];
        $tickers = $json['tickers'];

        // Gets user's balance
        $balance = $this->getBalance($user);

        // Gets current portfolio value
        $portfolioValue = $this->getPortfolioValue($data, $tickers);

        // Gets last daily close portfolio value
        $portfolioLastCloseValue = $this->getPortfolioLastCloseValue($data, $tickers);

        // Gets percentage change between current and last daily close portfolio value
        $percentageChange = $this->getPercentageChange($portfolioValue, $portfolioLastCloseValue);

        return view('home', [
            'balance' => $balance,
            'portfolioValue' => $portfolioValue,
            'portfolioLastCloseValue' => $portfolioLastCloseValue,
            'percentageChange' => $percentageChange,
        ]);
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
     * Gets percentage change between current and last daily close portfolio value
     * 
     * @param decimal Current portfolio value
     * @param decimal Last daily close portfolio value
     * @return decimal Percentage change
     */
    public function getPercentageChange($current, $lastClose) 
    {
        return number_format((($lastClose - $current) / $current) * 100, 3); // 3 values after decimal point
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
