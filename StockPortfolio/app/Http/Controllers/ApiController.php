<?php

namespace App\Http\Controllers;

use App\FinanceAPI;
use App\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Config;

class ApiController extends Controller
{
    public function buyStock(Request $request)
    {

        $stockInfo = FinanceAPI::getAllStockInfo(explode(",", $request->input("ticker")));
        $user = auth('api')->user(); //returns null if not valid
        if (!$user) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        elseif (!is_numeric($request->input("quantity")) || !isset($stockInfo["data"]))
        {
            return response()->json(['error' => "invalid ticker or quantity"], 400);
        }

        elseif (!UserUtility::canBuyShares($user, $stockInfo, $request->input("quantity")))
        {
            return response()->json(['error' => 'insufficient cash'], 403);
        }

        else
        {
            $user = auth('api')->user();
            UserUtility::storeStock($user, $stockInfo, $request->input("quantity"));
            return response()->json(['cashleft' => $user->portfolios->cash_owned], 200);
        }
    }

    /**
     * Provides an array of all user associated stocks.
     * If the user does not own any stocks, an empty array
     * is returned.
     *
     * Method type: GET
     * Success response: 200
     * Error response: 401
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStocks(Request $request)
    {
        $user = auth('api')->user(); //returns null if not valid
        if (!isset($user)) {
            return response()->json(['error' => 'invalid_token'], 401);
        } else {
            // All user stocks ordering by the stock id and only keeping the
            // ticker symbol, share count, and purchase price.
            $stocks = DB::table('users')
                ->join('portfolios', 'users.id', '=', 'portfolios.user_id')
                ->join('portfolio_stocks', 'portfolios.id', '=', 'portfolio_stocks.portfolio_id')
                ->where('users.id', '=', $user->id)
                ->select('ticker_symbol', 'share_count', 'purchase_price')
                ->orderby('portfolio_stocks.id')
                ->get();
            return response()->json($stocks, 200);
        }
    }

    /**
     * Performs sell share action against the database and returns 
     * the user's remaining cash.
     * 
     * Method type: POST
     * Success response: 200
     * Error response: 400 => invalid_token
     *                 401 => invalid_ticker_or_quantity
     *                 403 => insufficient_cash
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellStock(Request $request)
    {
        // Loads user; returns null if invalid
        $user = auth('api')->user();

        // Loads inputs
        $ticker = $request->input("ticker");
        $quantity = $request->input("quantity");

        // Checks if user is invalid
        if (!isset($user)) {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        // Checks if user has enough cash to sell
        if (!$this->hasEnoughCash($user)) {
            return response()->json(['error' => 'insufficient_cash'], 403);
        }

        // Checks if ticker and quantity are valid
        if (!$this->isValidSellTicker($user, $ticker) || !$this->isValidSellQuantity($user, $quantity, $ticker)) {
            return response()->json(['error' => 'invalid_ticker_or_quantity'], 400);
        }
        
        // Sells shares
        UserUtility::sellShares($user, $request->input("ticker"), $request->input("quantity"));
        return response()->json(['cashleft' => $user->portfolios->cash_owned], 200);
    }

    /**
     * Checks if user is available to sell its stock(s) by verifying
     * if the cash he owns is greater than the transaction fee
     *
     * @param $user Authenticated user
     * @return bool True if user has enough cash else false
     */
    private function hasEnoughCash($user)
    {
        // Load cash_owned data
        $cash = $user->portfolios->cash_owned;

        // Check if user's cash is greater than the transaction fee
        if ($cash < Config::get('constants.options.TRANSACT_COST')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Checks if quantity is valid
     * 
     * @param $user Authenticated user
     * @param integer Quantity number
     * @param string Ticker name
     * @return bool True if it's valid else false
     */
    private function isValidSellQuantity($user, $quantity, $ticker) 
    {
        // Load all metadata
        $stocks = $user->portfolios->portfolio_stocks;

        // Check if quantity is a number
        if (!is_numeric($quantity)) {
            return false;
        }

        // Check if quantity is lesser or equal than the company's share count
        foreach ($stocks as $stock) {
            if ($ticker === $stock->ticker_symbol && $quantity <= $stock->share_count) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if ticker is valid
     * 
     * @param $user Authenticated user
     * @param string Ticker name
     * @return bool True if it's valid else false
     */
    private function isValidSellTicker($user, $ticker)
    {
        // Load all metadata
        $stocks = $user->portfolios->portfolio_stocks;

        // Check if ticker symbol exists in the database
        foreach ($stocks as $stock) {
            if ($ticker === $stock->ticker_symbol) {
                return true;
            }
        }

        return false;
    }
}
