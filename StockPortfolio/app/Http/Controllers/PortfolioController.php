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

        // Gets portfolio value
        $portfolioValue = $this->getPortfolioValue($user);

        return view('home', [
            'portfolioValue' => $portfolioValue,
        ]);
    }

<<<<<<< HEAD
=======
    /**
     * Gets total sum of all current portfolio value
     * 
     * @return totalValue Sum of all current portfolio value
     */
    public function getPortfolioValue($user)
    {
        // Gets authenticated user's metadatas
        $user->portfolios->portfolio_stocks;

        // Loads all metadata
        $stocksData = $user->portfolios->portfolio_stocks;

        // Gets all user's share counts
        $tickers = $this->getShareCount($stocksData);

        // Gets all user's current total price for each company
        $prices = $this->getAllPrices($tickers);

        // Returns sum of all prices
        return $this->sumAll($prices);
    }

>>>>>>> Add getPortfolioValue function in PortfolioController
    /**
<<<<<<< HEAD
     * Gets total sum of all current portfolio value
     * 
     * @return totalValue Sum of all current portfolio value
     */
    public function getPortfolioValue($user)
    {
        // Get authenticated user's metadatas
        $user->portfolios->portfolio_stocks;
        $stocks = $user->portfolios->portfolio_stocks;

        // Sum all purchase_price to determine the portfolio value
        $totalValue = 0;
        foreach ($stocks as $value) {
            $totalValue += $value->purchase_price;
        }

        return $totalValue;
    }

    /**
=======
>>>>>>> Modify getBalance()
     * Gets user balance
     *
     * @param user Authenticated user
     * @return cash_owned
     */
    public function getBalance($user)
    {
        // Not sure if this works properly yet
<<<<<<< HEAD
<<<<<<< HEAD
        return $user->portfolios->cash_owned;
=======
        $portfolio = User::find($username)->portfolios();
        return $portfolio->cash_owned;
>>>>>>> Add getPortfolioValue function in PortfolioController
=======
        return $user->portfolios->cash_owned;
>>>>>>> Modify getBalance()
    }

    /**
     * Stores share count of each company that the authenticated user bought
     * 
     * @param metadata 
     * @return array
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
     * @param array Array of share counts
     * @return array Array of total price
     */
    private function getAllPrices(array $shareCount)
    {
        // TODO: Rename 'getStockInfo' function name when supported function is coded in FinanceAPI 
        $strJson = FinanceAPI::getStockInfo(array_keys($shareCount));
        $json = json_decode($strJson)->data;

        $currentPrices = array();
        foreach ($json as $value) { // Loop through Json 
            foreach ($shareCount as $key => $share) { // Loop through all user's share count
                if ($value === $key) {
                    array_push($currentPrices, $value->price * $share);
                }
            }
        }

        return $currentPrices;
    }

    /**
     * Sums all prices
     * 
     * @param array Array containing every prices
     * @return sum Sum of all prices
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
