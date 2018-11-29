<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Gets user balance
     *
     * @param user Authenticated user
     * @return cash_owned
     */
    public function getBalance($user)
    {
        // Haven't tested
        return $user->portfolios->cash_owned;
    }
}
