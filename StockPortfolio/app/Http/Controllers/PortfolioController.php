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
        // Loads metadata
        $json = $this->loadMetadata();
        $data = $json['info']['data'];
        $tickers = $json['tickers'];

        // Gets portfolio value
        $portfolioValue = $this->getPortfolioValue($data, $tickers);

        return view('home', [
            'portfolioValue' => $portfolioValue,
        ]);
    }

    /**
     * Gets total sum of all current portfolio value
     * 
     * @return totalValue Sum of all current portfolio value
     */
    public function getPortfolioValue($json, $tickers)
    {
        // Gets all user's current total price for each company
        $prices = $this->getAllPrices($json, $tickers);

        // Returns sum of all prices
        return $this->sumAll($prices);
    }

    /**
     * Gets user balance
     *
     * @param user Authenticated user
     * @return cash_owned
     */
    public function getBalance($user)
    {
        // Not sure if this works properly yet
        return $user->portfolios->cash_owned;
    }

    /**
     * Loads authenticated user's metadata
     * 
     * @return array Array which contains stock information and tickers 
     */
    private function loadMetadata()
    {
        // Gets current authenticated user
        $user = Auth::user();

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
     * Stores total price of each company in an array
     * 
     * @param json Json containing stock information from API
     * @param array Array of share counts
     * @return array Array of total price
     */
    private function getAllPrices($json, array $shareCount)
    {
        $currentPrices = array();
        foreach ($json as $value) { // Loop through Json 
            foreach ($shareCount as $key => $share) { // Loop through all user's share count
                if ($value['symbol'] === $key) { // Check if symbols are matching
                    array_push($currentPrices, $value['price'] * $share);
                }
            }
        }

        return $currentPrices;
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
