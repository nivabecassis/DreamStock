<?php

namespace App\Http\Controllers;

use Auth;
use App\FinanceAPI;
use App\CurrencyConverter;
use App\Http\Controllers\PortfolioController;

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
        $shareCount = [];
        foreach($dbStocks as $stock) {
            $ticker = $stock->ticker_symbol;
            $data = FinanceAPI::getStockInfo($ticker)['data'][0];
            $this->convertPricesToUSD($data);

            // Add the data to new arrays
            array_push($stocks, $data);
            array_push($shareCount, $stock->share_count);
        }

        $portfolio = [
            'cash' => $user->portfolios->cash_owned,
            'value' => PortfolioController::getPortfolioValue($stocks, $shareCount),
            'closeValue' => PortfolioController::getPortfolioLastCloseValue($stocks, $shareCount),
        ];

        return [
            'user' => $user,
            'portfolio' => $portfolio,
            'stocks' => $stocks,
        ];
    }

    /**
     * Convert the currency of all the prices if it is not in USD. Param
     * is passed by reference -> no need to reassign any values.
     *
     * @param $data JSON object containing the data from the API response.
     */
    private function convertPricesToUSD(&$data) {
        $currency = $data['currency'];
        if($currency != "USD") {
            $price = CurrencyConverter::convertToUSD($currency, $data['price']);
            $lastClose = CurrencyConverter::convertToUSD($currency, $data['close_yesterday']);

            // Reset the $data contents
            $data['price'] = $price;
            $data['close_yesterday'] = $lastClose;
            $data['currency'] = "USD";
        }
    }

}
