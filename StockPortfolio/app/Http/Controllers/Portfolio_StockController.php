<?php

namespace App\Http\Controllers;

use App\Portfolio_Stock;
use App\ApiUri;
use App\CurrencyConverter;
use App\FinanceAPI;
use App\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
 * Portfolio_Stock controller
 *
 * @author Austin Antoine
 */

class Portfolio_StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function quotes(Request $request)
    {
        $allQuotes = FinanceAPI::getAllStockInfo(explode(",", $request->input("ticker_symbol")));
        // Need to display quotes
        return view("buying_stocks.get_quotes", [
            'quotes' => $allQuotes
        ]);
    }

    /**
     * Adds entry to Portfolio_Stock table
     *
     * @param Request $request
     */
    function purchaseStock(Request $request, $symbol)
    {
        $user = Auth::user();
        UserUtility::buyStock($user, $request, $symbol);

        // *ISSUE* all quotes the user got will be gone when redirected
        return redirect("/home");
    }
}
