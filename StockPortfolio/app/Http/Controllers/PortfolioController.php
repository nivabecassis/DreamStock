<?php

namespace App\Http\Controllers;

use Auth;
use App\User;

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
        // Get current authenticated user
        $user = Auth::user();

        // Get portfolio value
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
}
