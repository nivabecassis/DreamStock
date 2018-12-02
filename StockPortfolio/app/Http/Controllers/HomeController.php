<?php

namespace App\Http\Controllers;

use App\Portfolio_Stock;
use App\UserUtility;
use Auth;
use App\FinanceAPI;
use App\CurrencyConverter;
use Illuminate\Http\Request;

use App\ApiUri;

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

    public function transaction(Request $request, $symbol)
    {
        // Good at this point
//        return var_dump($symbol);
        $type = $this->sanitize($request->input('type'));
        if (isset($type) && is_string($type)) {
            if (strtolower($type) === 'sell') {
                $data = $this->getDataForView();
                $data['stockToSell'] = $this->getStockFromStocks($symbol, $data['stocks']);
//                return var_dump($data['stockToSell']);
                // Good at this point
                return view('home', $data);
            } else if (strtolower($type) === 'buy') {
                // TODO: Austin's stuff goes here
            }
        }
        // TODO: make this view (maybe 404)
        return view('error');
    }

    /**
     * Find a stock out of many stocks
     *
     * @param $symbol
     * @param $stocks array haystack
     * @return mixed Stock formatted as the api response
     */
    private function getStockFromStocks($symbol, $stocks)
    {
        foreach ($stocks as $stock) {
            if ($stock['symbol'] === $symbol) {
                return $stock;
            }
        }
    }

    /**
     * Sells the user's given stock if permitted.
     *
     * @param Request $request
     * @param $symbol
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sell(Request $request, $symbol)
    {
        $user = Auth::user();

        // Access the share count from the form
        $shareCount = $request->input('share_count');

        // Execute the sale, validation is done within this function
        UserUtility::sellShares($user, $symbol, $shareCount);

        // Get the portfolio data for the view
        $data = $this->getDataForView();

        return redirect()->route('home', $data);
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

        // Array of stocks (comes from database)
        $dbStocks = $portfolio->portfolio_stocks;

        // Array of ticker symbols
        $tickers = $this->getTickers($dbStocks);

        // Array of stock data (comes from API call)
        $stocksData = FinanceAPI::getAllStockInfo($tickers)['data'];

        // Returns a single array containing all the necessary information
        // for stocks with pricing in USD.
        $stocks = $this->getStocksInfo($dbStocks, $stocksData);

        // Share count for each stock
        $shareCounts = $this->getShareCounts($stocks);

        // More details on the portfolio includes: cash_owned, value, last close value
        $portfolioDetails = $this->getPortfolioData($user, $stocks, $shareCounts);

        return [
            'user' => $user,
            'portfolio' => $portfolioDetails,
            'stocks' => $stocks,
            'since' => self::getDateFromTimestamp($user->created_at),
        ];
    }

    /**
     * @param $dbStocks
     * @param $stocksData
     * @return array
     */
    private function getStocksInfo($dbStocks, $stocksData)
    {
        $stocks = array();
        foreach ($dbStocks as $dbStock) {
            foreach ($stocksData as $data) {
                // Matching data from API and from database
                if ($dbStock->ticker_symbol === $data['symbol']) {
                    // Updated stock object with all the data needed
                    $stock = $this->keepNecessaryInfo($dbStock, $data);
                    // Convert all the pricing to USD
                    $this->convertPricesToUSD($stock);
                    // Add the individual stock data to the array
                    array_push($stocks, $stock);
                }
            }
        }
        return $stocks;
    }

    /**
     * Get an array of ticker symbols from an array of stocks.
     *
     * @param $stocks array of stocks
     * @return array of tickers
     */
    private function getTickers($stocks)
    {
        $tickers = array();
        foreach ($stocks as $stock) {
            array_push($tickers, $stock->ticker_symbol);
        }
        return $tickers;
    }

    /**
     * Gets the share count for each stock.
     * The returned associative array has key as symbol and value as
     * share count.
     *
     * @param $stocks array associative containing stock data
     * @return array associative containing symbols => count
     */
    private function getShareCounts($stocks)
    {
        $shareCounts = array();
        foreach ($stocks as $stock) {
            $shareCounts[$stock['symbol']] = $stock['count'];
        }
        return $shareCounts;
    }

    /**
     * This function only keeps the necessary info for the app. If
     * the view needs more info, add here.
     *
     * @param $stock Portfolio_Stock model object
     * @param $data JSON object containing extra data on this stock
     * @return array contains info about the stock
     */
    private function keepNecessaryInfo($stock, $data)
    {
        return [
            'id' => $stock->id,
            'count' => $stock->share_count,
            'symbol' => $data['symbol'],
            'company' => $data['name'],
            'currency' => $data['currency'],
            'price' => $data['price'],
            'close_yesterday' => $data['close_yesterday'],
            'change' => $data['day_change'],
        ];
    }

    /**
     * Retrieves more information of the user's portfolio according
     * to their stocks.
     *
     * @param $user User contains stored information from database
     * @param $stocks array of JSON stock objects
     * @param $shareCounts array associative . Key is the ticker symbol,
     * value is the share count for the corresponding symbol.
     * @return array containing portfolio details
     */
    private function getPortfolioData($user, $stocks, $shareCounts)
    {
        $portfolioController = new PortfolioController();
        return [
            'cash' => $user->portfolios->cash_owned,
            'value' => $portfolioController->getPortfolioValue($stocks, $shareCounts),
            'closeValue' => $portfolioController->getPortfolioLastCloseValue($stocks, $shareCounts),
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
            $data['orig_currency'] = $currency;
            $data['orig_price'] = $data['price'];
            $data['orig_close_yesterday'] = $data['close_yesterday'];

            $price = CurrencyConverter::convertToUSD($currency, $data['price']);
            $lastClose = CurrencyConverter::convertToUSD($currency, $data['close_yesterday']);

            // Reset the $data contents
            $data['price'] = $price;
            $data['close_yesterday'] = $lastClose;
            $data['currency'] = "USD";
        }
    }

    /**
     * Extracts the date from a timestamp.
     * @param $tm string timestamp to extract from
     * @return string date
     */
    public static function getDateFromTimestamp($tm)
    {
        $pos = strpos($tm, ' ');
        return substr($tm, 0, $pos);
    }

    private function sanitize($str)
    {
        $str = strip_tags($str);
        $str = htmlentities($str);
        return $str;
    }

}
