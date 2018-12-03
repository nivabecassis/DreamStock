<?php

namespace App\Http\Controllers;

use App\ApiUri;
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
        $GLOBALS["allQuotes"] = FinanceAPI::getAllStockInfo(explode(",", $request->input("ticker_symbol")));
        // Need to display quotes
        return view("buying_stocks.get_quotes", [
            'quotes' => $GLOBALS["allQuotes"]
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
        $quote = $this->findCompanyInfo($GLOBALS["allQuotes"], $symbol);
        $shares = $request->input("shares");

        UserUtility::storeStock($user, $quote, $symbol, $shares);

        // *ISSUE* all quotes the user got will be gone when redirected
        return redirect("/home");
    }

    private function findCompanyInfo($allQuotes, $symbol)
    {
        foreach ($allQuotes["data"] as $quote) {
            if ($quote["symbol"] === $symbol) {
                return $quote;
            }
        }
    }
}
