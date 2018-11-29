<?php

namespace App\Http\Controllers;

use App\Portfolio_Stock;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

/*
 * Portfolio_Stock controller
 *
 * @author Austin Antoine
 * @author Stephen Kwan
 * @author Niv Abecassis
 * @author Yehoshua Fish
 */
class Portfolio_StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Adds entry to Portfolio_Stock table
     *
     * @param Request $request
     */
    function purchaseStock(Request $request){
        if (isset($_POST["purchase_price"])) {
            $portfolio_stock = new Portfolio_Stock();
            $portfolio_stock->ticker_symbol = $request->input("symbol");
            $portfolio_stock->portfolio_id = User::find(Auth::id())->portfolios()->id;
            $portfolio_stock->share_count = $request->input("share_count");
            $portfolio_stock->purchase_date = date("Y-m-d H:i:s");
            $portfolio_stock->purchase_price = $_POST["purchase_price"];
            $portfolio_stock->save();
        }
    } // purchaseStock()

    /**
     * Update record in Portfolio_Stock
     *
     * @param Request $request
     */
    function updateStock(Request $request) {
        $portfolio_stock = Portfolio_Stock::where("portfolio_id", "=", User::find(Auth::id())->portfolios()->id);
        $portfolio_stock->share_count = $request->input("share_count");
        $portfolio_stock->save();
    } // updateStock()
}
