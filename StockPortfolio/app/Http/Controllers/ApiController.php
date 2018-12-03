<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function buyStock(Request $request)
    {

        $user = auth('api')->user(); //returns null if not valid
        if (!$user) {
            return response()->json(['error' => 'invalid_token'], 401);
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
}
