<?php

namespace App\Http\Controllers;

use App\Portfolio_Stock;
use App\ApiUri;
use App\CurrencyConverter;
use App\FinanceAPI;
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
        $stocks = $user->portfolios->portfolio_stocks;
        $stockInfo = FinanceAPI::getAllStockInfo(explode(",", $symbol));
        $shares = $request->input("shares");


        if ($this->canBuyShares($stockInfo, $shares))
        {
            /*
             * Stock is successfully being saved to the database but for some reason, there
             * user's cash isn't decreasing in the database. Need to find out how to update
             */
            $user->portfolios->cash_owned -= 10;
            $user->portfolios->cash_owned -= $this->getPurchasePrice($stockInfo, $shares);

            if ($stocks->count() < 5 && !$user->portfolios->portfolio_stocks
                ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first())
            {
                /* Create new record and set fields */
                $portfolio_stock = new Portfolio_Stock();
                $portfolio_stock->ticker_symbol = $symbol;
                $portfolio_stock->portfolio_id = $user->portfolios->id;
                $portfolio_stock->share_count = $shares;
                $portfolio_stock->purchase_date = date("Y-m-d H:i:s");
                $portfolio_stock->purchase_price = $stockInfo["data"][0]["price"];
                $portfolio_stock->save();
            }

            elseif ($stocks->count() <= 5 && $user->portfolios->portfolio_stocks
                    ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first())
            {
                $this->updateStock($stockInfo, $shares);
            }

            else
            {
                redirect("/home");
            }
        }

        // *ISSUE* all quotes the user got will be gone when redirected
        return redirect("/home");
    }

    /**
     * Update record in Portfolio_Stock
     *
     * @param Request $request
     */
    function updateStock($stockInfo, $shares)
    {
        /* Haven't tested */
        $user = Auth::user();

        // Transaction fee
        $user->portfolios->cash_owned -= 10;

        // Subtract price of purchasing shares
        $user->portfolios->cash_owned -= $this->getPurchasePrice($stockInfo, $shares);
        $portfolio_stock = $user->portfolios->portfolio_stocks->where(
            "ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first();

        $portfolio_stock->share_count += $shares;
        $user->portfolios->save();
        $portfolio_stock->save();

    }

    /**
     * Checks if the user can buy the stock
     *
     * @param Request $request
     * @return bool Whether user can buy or not
     */
    private function canBuyShares($stockInfo, $shares)
    {
        $user = Auth::user();
        $stocks = $user->portfolios->portfolio_stocks;
        $stockCount = $stocks->where("portfolio_id", "=", $user->portfolios->id)->count();

        /*
         * If the user already has 5 stocks and the one they're trying to purchase shares from
         * isn't one of the stocks that they already own, they cannot buy the stock
         */
        if ($stockCount === 5 && !$user->portfolios->where("ticker_symbol", "=",
                $stockInfo["data"][0]["symbol"])->first())
        {
            return false;
        }

        $priceUSD = CurrencyConverter::convertToUSD($stockInfo["data"][0]["currency"], $stockInfo["data"][0]["price"]);

        if ($user->portfolios->cash_owned - 10 >= $priceUSD * $shares) {
            return true;
        } else {
            return false;
        }

        // This will be the case when the user can update
        return true;
    }

    /**
     * Gets the total purchase price of the shares
     *
     * @param Request $request
     * @return float|int Price
     */
    private function getPurchasePrice($stockInfo, $shares)
    {
        // How much purchasing the shares costs
        return $stockInfo["data"][0]["price"] * $shares;
    }
}
