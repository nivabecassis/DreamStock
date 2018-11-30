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

        // Gets all user's tickers
        $tickers = $this->getAllTickers($stocksData);

        // Gets all user's prices
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
     * Gets all ticker symbols from authenticated user
     * 
     * @param metadata User's metedata
     * @return array Array of company tickers
     */
    private function getAllTickers($metadata) 
    {
        $companiesTicker = array();
        foreach ($metadata as $value) {
            array_push($companiesTicker, $value->ticker_symbol);
        }

        return $companiesTicker;
    }

    /**
     * Gets all prices from authenticated user
     * 
     * @param tickers User's tickers
     * @return array Array of current prices
     */
    private function getAllPrices(array $tickers)
    {
        // TODO: Rename 'getStockInfo' function name when supported function is coded in FinanceAPI 
        $strJson = FinanceAPI::getStockInfo($tickers);
        $json = json_decode($strJson)->data;

        $currentPrices = array();
        foreach ($json as $value) {
            array_push($currentPrices, $value->price);
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
