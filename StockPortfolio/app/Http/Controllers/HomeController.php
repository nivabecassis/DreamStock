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
     * @return array with array containing the data to be displayed on home page.
     */
    private function getDataForView()
    {
        $user = Auth::user();
        $portfolio = $user->portfolios;
        $dbStocks = $portfolio->portfolio_stocks;

        // Get more information for each user owned stock
        $stocks = [];
        $shareCount = [];
        foreach ($dbStocks as $stock) {
            $ticker = $stock->ticker_symbol;
            $data = FinanceAPI::getStockInfo($ticker)['data'][0];
            $this->convertPricesToUSD($data);

            // Add the data to new arrays
            array_push($stocks, $data);
            array_push($shareCount, $stock->share_count);
        }

        $portfolio = $this->getPortfolioData($user, $stocks, $shareCount);

        return [
            'user' => $user,
            'portfolio' => $portfolio,
            'stocks' => $stocks,
        ];
    }

    /**
     * Retrieves more information of the user's portfolio according
     * to their stocks.
     *
     * @param $user user contains generic information
     * @param $stocks array of JSON stock objects
     * @param $shareCount associative array. Key is the ticker symbol,
     * value is the share count for the corresponding symbol.
     * @return array containing portfolio details
     */
    private function getPortfolioData($user, $stocks, $shareCount)
    {
        return [
            'cash' => $user->portfolios->cash_owned,
            'value' => PortfolioController::getPortfolioValue($stocks, $shareCount),
            'closeValue' => PortfolioController::getPortfolioLastCloseValue($stocks, $shareCount),
        ];
    }

    /**
     * Convert the currency of all the prices if it is not in USD. Param
     * is passed by reference -> no need to reassign any values.
     *
     * @param $data JSON object containing the data from the API response.
     */
    private function convertPricesToUSD(&$data)
    {
        $currency = $data['currency'];
        if ($currency != "USD") {
            $price = CurrencyConverter::convertToUSD($currency, $data['price']);
            $lastClose = CurrencyConverter::convertToUSD($currency, $data['close_yesterday']);

            // Reset the $data contents
            $data['price'] = $price;
            $data['close_yesterday'] = $lastClose;
            $data['currency'] = "USD";
        }
    }

}
