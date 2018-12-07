<?php
/**
 * Created by PhpStorm.
 * User: nivabecassis
 * Date: 2018-12-01
 * Time: 7:15 PM
 */

namespace App;

use \Config;
use App\FinanceAPI;
use App\CurrencyConverter;

/**
 * Class UserUtility provides user related actions through the use
 * of public static functions.
 *
 * @package App
 */
class UserUtility
{

    /**
     * Sells the specified share count of the user if the transaction
     * is authenticated.
     *
     * @param $user User
     * @param $symbol
     * @param $count Int number of shares that will be sold
     * @return bool true if transaction is authorized. Otherwise, associative
     * array returned. Key: error code, value: error message.
     */
    public static function sellShares($user, $symbol, $count)
    {
        $stock = self::findMatchingStock($user, $symbol);
        $ownedShares = $stock->share_count;
        $response = false;
        if ($ownedShares > 0 && $ownedShares >= $count) {
            $amount = self::calcTotalStockValue($symbol, $count);
            if (self::performTransaction($user, $amount)) {
                // Transaction approved and executed
                $stock->share_count -= $count;
                $stock->save();

                // Delete the record if there are no shares left
                if ($stock->share_count == 0) {
                    $stock->delete();
                }
                $response = true;
            } else {
                $response = ['401' => 'Insufficient cash!'];
            }
        } else {
            $response = ['401' => "$symbol: Attempting to sell $count 
                shares. You own $ownedShares"];
        }
        return $response;
    }

    /**
     * Calculate the total stock value for a given stock. Pulls the
     * most recent price of the stock from the FinanceAPI class.
     *
     * @param $stock Stock
     * @param $count Int number of stocks
     * @return float|int Total value of the stocks
     */
    public static function calcTotalStockValue($symbol, $count)
    {
        $data = FinanceAPI::getAllStockInfo([$symbol])['data'][0];
        $currency = $data['currency'];
        $price = $data['price'];
        if ($currency !== 'USD') {
            $price = CurrencyConverter::convertToUSD($currency, $price);
        }
        return $price * $count;
    }

    /**
     * Finds the matching stock according to its id.
     *
     * @param $user User
     * @param $symbol
     * @return null if the stock is not found, Portfolio_Stock otherwise
     */
    public static function findMatchingStock($user, $symbol)
    {
        $stocks = $user->portfolios->portfolio_stocks;
        foreach ($stocks as $stock) {
            if ($stock->ticker_symbol === $symbol) {
                return $stock;
            }
        }
        return null;
    }

    /**
     * Gets the user's balance.
     *
     * @param $user User
     * @return double user balance
     */
    public static function getBalance($user)
    {
        $portfolio = $user->portfolios;
        return $portfolio != null ? $portfolio->cash_owned : 0;
    }

    /**
     * Add or remove the given amount to the user's balance.
     * Takes into account the transaction fee.
     *
     * @param $user
     * @param $amount int to add/remove to the balance
     * @return bool True if transaction is permitted, false otherwise
     */
    public static function performTransaction($user, $amount)
    {
        $allow = self::canAffordTransaction($user, $amount);
        if ($allow) {
            $user->portfolios->cash_owned += $amount
                - Config::get('constants.options.TRANSACT_COST');
            $user->portfolios->save();
        }
        return $allow;
    }

    /**
     * Checks if the user can afford the specified transaction.
     * Transaction is valid if the cash owned + the amount in
     * the transaction - the transaction cost is greater or equal
     * than zero.
     *
     * @param $user User
     * @param $amount Int amount of the transaction
     * @return bool True if the transaction should be permitted,
     * false otherwise.
     */
    public static function canAffordTransaction($user, $amount)
    {
        return $user->portfolios->cash_owned + $amount
            - Config::get('constants.options.TRANSACT_COST') >= 0;
    }

    /**
     * Does all necessary checking and will either buy a stock or update an
     * existing one
     *
     * @param Request $request
     * @param $symbol Ticker symbol of the company
     */
    public static function storeStock($user, $stockInfo, $shares)
    {
        $stocks = $user->portfolios->portfolio_stocks;
        $currency = $stockInfo["data"][0]["currency"];
        $price = $stockInfo["data"][0]["price"];
        $symbol = $stockInfo["data"][0]["symbol"];
        $maxStocks = Config::get('constants.options.MAX_STOCKS_FREE_VERSION');

        if (self::canBuyShares($user, $stockInfo, $shares)) {
            /*
             * Stock is successfully being saved to the database but for some reason, there
             * user's cash isn't decreasing in the database. Need to find out how to update
             */
            self::performTransaction($user, -(CurrencyConverter::convertToUSD($currency, $price) * $shares));

            if ($stocks->count() < $maxStocks && !$user->portfolios->portfolio_stocks
                    ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first()) {
                /* Create new record and set fields */
                $portfolio_stock = new Portfolio_Stock();
                $portfolio_stock->ticker_symbol = $symbol;
                $portfolio_stock->portfolio_id = $user->portfolios->id;
                $portfolio_stock->share_count = $shares;
                $portfolio_stock->purchase_date = date("Y-m-d H:i:s");
                $portfolio_stock->purchase_price = $stockInfo["data"][0]["price"];
                $portfolio_stock->weighted_price = $stockInfo["data"][0]["price"];
                $portfolio_stock->save();
            }

            if ($stocks->count() <= $maxStocks && $stocks
                    ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first()) {
                self::updateStock($user, $stockInfo, $shares);
            }
        }
    }

    /**
     * Checks if the user can buy the stock
     *
     * @param Request $request
     * @return bool Whether user can buy or not
     */
    public static function canBuyShares($user, $stockInfo, $shares)
    {
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
     * Update record in Portfolio_Stock. This will only be called by the buyStock
     * function
     *
     * @param Request $request
     */
    private static function updateStock($user, $stockInfo, $shares)
    {
        $portfolio_stock = $user->portfolios->portfolio_stocks->where(
            "ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first();

        $sharePrice = $stockInfo["data"][0]["price"];
        $portfolio_stock->share_count += $shares;
        $portfolio_stock->weighted_price = self::getWeightedPrice($portfolio_stock, $shares, $sharePrice);
        $user->portfolios->save();
        $portfolio_stock->save();
    }

    /**
     * Determines if the user has enough cash to make the purchase
     *
     * @param $user Checks if the authenticated user has enough money
     * @param $price Price to check if the user has enough money
     * @return bool Whether user has enough cash or not
     */
    public static function hasEnoughCash($user, $price)
    {
        return $user->portfolios->cash_owned - Config::get('constants.options.TRANSACT_COST') >= $price;
    }

    /**
     * Calculates the weighted price after updating the amount of shares
     * the user has for a company
     *
     * @param $user User to calculate weighted price for
     * @param $shares Amount of shares the user purchased
     * @param $price Price of each share
     * @return double New weighted price
     */
    public static function getWeightedPrice($portfolio_stock, $shares, $price)
    {
        $lastWeightedPrice = $portfolio_stock->weighted_price;
        $lastTotalShares = $portfolio_stock->share_count;
        $lastTotalPrice = $lastWeightedPrice * $lastTotalShares;
        $currentTotalPrice = ($shares * $price) + $lastTotalPrice;
        $currentTotalShares = $lastTotalShares + $shares;
        $newWeightedPrice = $currentTotalPrice / $currentTotalShares;

        return $newWeightedPrice;
    }

    /**
     * Checks if the user already has 5 stocks and is trying to purchase more shares
     * in a company that they don't already own shares in
     *
     * @param $user User to check
     * @return bool Whether they can't buy the shares or not
     */
    public static function hasMaxAndCantUpdate($user, $stockInfo)
    {
        $stocks = $user->portfolios->portfolio_stocks;
        $stockCount = $stocks->where("portfolio_id", "=", $user->portfolios->id)->count();
        $maxStocks = Config::get('constants.options.MAX_STOCKS_FREE_VERSION');

        /*
         * If the user already has 5 stocks and the one they're trying to purchase shares from
         * isn't one of the stocks that they already own, they cannot buy the stock
         */
        return $stockCount ===  $maxStocks && !$stocks->where("ticker_symbol", "=",
                $stockInfo["data"][0]["symbol"])->first();
    }

    /**
     * Gets percentage change between current and last daily close portfolio value
     *
     * @param decimal Current portfolio value
     * @param decimal Last daily close portfolio value
     * @return decimal Percentage change
     */
    public static function getPercentageChange($current, $lastClose)
    {
        if($lastClose > 0) {
            return number_format((($current - $lastClose) / $lastClose) * 100, 3); // 3 values after decimal point
        }
        return 0;
    }
}
