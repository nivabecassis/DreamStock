<?php

namespace App\Http\Controllers;

use App\Portfolio;
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

    public function quotes(Request $request)
    {
        $symbol = $this->sanitize($request->input("ticker_symbol"));
        if (is_string($symbol)) {
            $allQuotes = FinanceAPI::getAllStockInfo(explode(",", $symbol));
            $data = $this->getDataForView();
            $data["quotes"] = $allQuotes;

            return view("/home", $data);
        }
        return $this->error(['400' => 'Inputted symbol is invalid!']);
    }


    public function transaction(Request $request, $symbol)
    {
        $data = array();
        // Type of the request (buy or sell)
        $type = $this->sanitize($request->input('type'));
        if (isset($type) && is_string($type)) {
            $data = $this->getDataForView();
            $data['stockPerform'] = $this->getStockFromStocks($symbol, $data['stocks']);
            if (strtolower($type) == 'sell') {
                $data['action'] = 'sell';
            } else if (strtolower($type) == 'buy') {
                $data['action'] = 'buy';
            } else {
                $this->error(['400' => 'Invalid request']);
            }
        } else {
            $data = [
                'error' => 'true',
                'errorMsg' => 'Invalid request!',
            ];
        }
        return view('home', $data);
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

        if (is_numeric($shareCount)) {
            $shareCount = floor($shareCount);
            // Execute the sale, validation is done within this function
            $response = UserUtility::sellShares($user, $symbol, $shareCount);
            if ($response !== true) {
                // String returned from sellShares is an error message
                return $this->error($response);
            }
        } else {
            return $this->error(['400' => 'Invalid number of stocks entered']);
        }

        // Get the portfolio data for the view
        $data = $this->getDataForView();

        return redirect()->route('home', $data);
    }

    /**
     * Adds entry to Portfolio_Stock table
     *
     * @param Request $request
     * @param $symbol Ticker of company
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    function purchaseStock(Request $request, $symbol)
    {
        $user = Auth::user();
        $quote = FinanceAPI::getAllStockInfo(explode(",", $symbol));
        $shares = $request->input("share_count");
        $cost = CurrencyConverter::convertToUSD($quote["data"][0]["currency"], $quote["data"][0]["price"]) * $shares;

        if (!UserUtility::hasEnoughCash($user, $cost)) {
            return $this->error(['400' => 'You didn\'t have enough cash to complete the last purchase']);
        }

        if (UserUtility::hasMaxAndCantUpdate($user, $quote)) {
            return $this->error(['400' => 'You already have shares with 5 different companies']);
        }

        if (is_numeric($shares)) {
            $shares = floor($shares);
            UserUtility::storeStock($user, $quote, $shares);
        } else {
            return $this->error(['400' => 'Invalid number of stocks entered']);
        }

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
        $data = array();
        if (!isset($user->portfolios) || count($user->portfolios->portfolio_stocks) == 0) {
            $data = $this->showNoData($user);
            $data['portfolio'] = $this->getPortfolioData($user);
        } else {
            $data = $this->showData($user);
        }
        return $data;
    }

    /**
     * Set up of the data needed for the homepage if the user has no data yet.
     *
     * @param $user User that is currently authenticated
     * @return array with error details
     */
    private function showNoData($user)
    {
        // Create portfolio for the new user and give default balance
        if (!isset($user->portfolios)) {
            $this->createPortfolio($user);
        }
        return [
            'error' => 'true',
            'errorMsg' => 'No stocks to show!',
        ];
    }

    /**
     * Creates a new portfolio for the given user.
     * Assigns it the default balance value coming from the
     * config\constants.php file.
     *
     * @param $user User
     */
    private function createPortfolio($user)
    {
        $portfolio = new Portfolio();
        $portfolio->user_id = $user->id;
        $portfolio->cash_owned = \Config::get('constants.options.DEFAULT_BALANCE');

        $user->portfolios = $portfolio;
        $user->portfolios->save();
    }

    /**
     * Performs all necessary actions related to getting the data associated
     * with the authenticated user. Actions include fetching the user's owned
     * stocks and their portfolio details.
     *
     * @param $user User that is currently authenticated
     * @return array containing all data necessary for the homepage.
     */
    private function showData($user)
    {
        $portfolio = $user->portfolios;

        // Array of stocks (comes from database)
        $dbStocks = $portfolio->portfolio_stocks;

        // Array of ticker symbols
        $tickers = $this->getTickers($dbStocks);

        $stocks = array();
        $shareCounts = array();
        // Get data associated with each stock
        if (count($tickers) > 0) {
            // Array of stock data (comes from API call)
            $stocksData = FinanceAPI::getAllStockInfo($tickers)['data'];
            // Returns a single array containing all the necessary information
            // for stocks with pricing in USD.
            $stocks = $this->getStocksInfo($dbStocks, $stocksData);
            // Share count for each stock
            $shareCounts = $this->getShareCounts($stocks);
        }

        // More details on the portfolio includes: cash_owned, value, last close value
        $portfolioDetails = $this->getPortfolioData($user, $stocks, $shareCounts);

        return [
            'user' => $user,
            'portfolio' => $portfolioDetails,
            'stocks' => $stocks,
        ];
    }

    /**
     * Perform a series of operations on the stocks.
     * 1) Keep the data that is relevant for the homepage.
     * 2) Convert pricing to USD (some fields remain with the
     * original pricing and currency in case it is needed).
     *
     * @param $dbStocks array of stocks coming from the database
     * @param $stocksData array of stocks data coming from API
     * @return array Contains the combined data from the stocks
     * provided from the database and those coming from the API.
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
            'change' => $data['change_pct'],
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
    private function getPortfolioData($user, $stocks = array(), $shareCounts = array())
    {
        $portfolioController = new PortfolioController();

        $value = 0;
        $closeValue = 0;
        $portfolioChange = 0;
        if (count($stocks) > 0 && count($shareCounts) > 0) {
            $value = $portfolioController->getPortfolioValue($stocks, $shareCounts);
            $closeValue = $portfolioController->getPortfolioLastCloseValue($stocks, $shareCounts);
            $portfolioChange = UserUtility::getPercentageChange($value, $closeValue);
        }

        return [
            'cash' => $user->portfolios->cash_owned,
            'since' => self::getDateFromTimestamp($user->created_at),
            'value' => $value,
            'closeValue' => $closeValue,
            'portfolioChange' => $portfolioChange,
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

    /**
     * Strips any html tags and returns the string.
     *
     * @param string $str string to validate
     * @return string after sanitation
     */
    private function sanitize($str)
    {
        $str = strip_tags($str);
        $str = htmlentities($str);
        return $str;
    }

    /**
     * Redirect to the error page with the given. Default is 500.
     *
     * @param array $errors errors encountered. Associative array: key = code
     * value = message.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function error($errors = array())
    {
        return view('common.errors', ['errors' => $errors]);
    }

}
