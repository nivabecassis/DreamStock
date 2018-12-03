<?php
/**
 * Created by PhpStorm.
 * User: nivabecassis
 * Date: 2018-12-01
 * Time: 7:15 PM
 */

namespace App;

use \Config;

/**
 * Class UserUtility provides user related actions through the use
 * of public static functions.
 *
 * @package App
 */
class UserUtility
{

    /**
     * Gets the user's balance.
     *
     * @param $user User
     * @return double user balance
     */
    public static function getBalance($user) {
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
     * @return null This will be returned if there is no purchase made
     */
    public static function buyStock($user, $stockInfo, $ticker, $shares)
    {
        $stocks = $user->portfolios->portfolio_stocks;
        $currency = $stockInfo["data"][0]["currency"];
        $price = $stockInfo["data"][0]["price"];

        if (self::canBuyShares($user, $stockInfo, $shares))
        {
            /*
             * Stock is successfully being saved to the database but for some reason, there
             * user's cash isn't decreasing in the database. Need to find out how to update
             */
            self::performTransaction($user, CurrencyConverter::convertToUSD($currency, $price) * $shares);

            if ($stocks->count() < 5 && !$user->portfolios->portfolio_stocks
                    ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first())
            {
                /* Create new record and set fields */
                $portfolio_stock = new Portfolio_Stock();
                $portfolio_stock->ticker_symbol = $ticker;
                $portfolio_stock->portfolio_id = $user->portfolios->id;
                $portfolio_stock->share_count = $shares;
                $portfolio_stock->purchase_date = date("Y-m-d H:i:s");
                $portfolio_stock->purchase_price = $stockInfo["data"][0]["price"];
                $portfolio_stock->save();
            }

            if ($stocks->count() <= 5 && $user->portfolios->portfolio_stocks
                    ->where("ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first())
            {
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

        if ($user->portfolios->cash_owned - 10 >= $priceUSD * $shares)
        {
            return true;
        }

        else
        {
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
        $currency = $stockInfo["data"][0]["currency"];
        $price = $stockInfo["data"][0]["price"];

        self::performTransaction($user, CurrencyConverter::convertToUSD($currency, $price) * $shares);
        $portfolio_stock = $user->portfolios->portfolio_stocks->where(
            "ticker_symbol", "=", $stockInfo["data"][0]["symbol"])->first();

        $portfolio_stock->share_count += $shares;
        $user->portfolios->save();
        $portfolio_stock->save();

    }
}
