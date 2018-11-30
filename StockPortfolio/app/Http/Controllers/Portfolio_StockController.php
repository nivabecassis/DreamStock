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
        $allQuotes = json_decode(FinanceAPI::getAllStockInfo($request->input("tickers")));
        return view('buying_stocks.quotes', [
            'quotes' => $allQuotes,
        ]);
    }

    /**
     * Adds entry to Portfolio_Stock table
     *
     * @param Request $request
     */
    function purchaseStock(Request $request)
    {
        /* Haven't tested */

        $user = Auth::user();
        $stocks = $user->portfolios->portfolio_stocks;

        if ($this->canBuyShares($request) && $stocks->ticker_symbol->count() < 5)
        {
            /* Create new record and set fields */
            $portfolio_stock = new Portfolio_Stock();
            $portfolio_stock->ticker_symbol = $request->input("ticker_symbol");
            $portfolio_stock->portfolio_id = $user->portfolios->id;
            $portfolio_stock->share_count = $request->input("share_count");
            $portfolio_stock->purchase_date = date("Y-m-d H:i:s");
            $portfolio_stock->purchase_price = $this->getPurchasePrice($request);
            $portfolio_stock->save();

        }
    }

    /**
     * Update record in Portfolio_Stock
     *
     * @param Request $request
     */
    function updateStock(Request $request)
    {
        /* Haven't tested */

        if ($this->canBuyShares($request))
        {
            $user = Auth::user();

            // Transaction fee
            $user->portfolios->cash_owned -= 10;

            // Subtract price of purchasing shares
            $user->portfolios->cash_owned -= $this->getPurchasePrice($request);
            $portfolio_stock = $user->portfolios->portfolio_stocks->where(
                "ticker_symbol", "=", $request->input("ticker_symbol"));

            $portfolio_stock->share_count += $request->input("share_count");
            $portfolio_stock->save();
        }
    }

    /**
     * Checks if the user can buy the stock
     *
     * @param Request $request
     * @return bool Whether user can buy or not
     */
    private function canBuyShares(Request $request)
    {
        $user = Auth::user();
        $stocks = $user->portfolios->portfolio_stocks;
        $converter = new CurrencyConverter();
        $stockCount = $stocks->ticker_symbol->count();

        /*
         * If the user already has 5 stocks and the one they're trying to purchase shares from
         * isn't one of the stocks that they already own, they cannot buy the stock
         */
        if ($stockCount === 5 && !$user->portfolios->where("ticker_symbol", "=",
                $request->input("ticker_symbol")->exists()))
        {
            return false;
        }

        $stockInfo = json_decode(FinanceAPI::getAllStockInfo($request->input("ticker_symbol")));
        $priceUSD = $converter->convertToUSD($stockInfo->data["currency"], $stockInfo->data["price"]);

        if ($user->portfolios->cash_owned - 10 >= $priceUSD * $request->input("share_count"))
        {
            return true;
        }

        else
        {
            return false;
        }
    }

    /**
     * Gets the total purchase price of the shares
     *
     * @param Request $request
     * @return float|int Price
     */
    private function getPurchasePrice(Request $request)
    {
        // How much purchasing the shares costs
        $stockInfo = json_decode(FinanceAPI::getAllStockInfo($request->input("ticker_symbol")));
        return $stockInfo->data["price"] * $request->input("share_count");
    }
}
