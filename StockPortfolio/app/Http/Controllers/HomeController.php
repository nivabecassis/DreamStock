<?php

namespace App\Http\Controllers;

use Auth;
use App\FinanceAPI;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home', $this->getDataForView());
    }

    /**
     * Get all the info that is needed for the the homepage information.
     * @return array containing the data for the view
     */
    private function getDataForView()
    {
        $user = Auth::user();
        $portfolio = $user->portfolios;

        // Get more information for each user owned stock
        $dbStocks = $portfolio->portfolio_stocks;
        $stocks = [];
        foreach($dbStocks as $stock) {
            $ticker = $stock->ticker_symbol;
            array_push($stocks, FinanceAPI::getStockInfo($ticker)['data']);
        }

        return [
            'user' => $user,
            'portfolio' => $portfolio,
            'stocks' => $stocks,
        ];
    }
}
