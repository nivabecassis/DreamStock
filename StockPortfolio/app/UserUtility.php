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
     * @param $stockId Int stock id of the share that needs to be sold
     * @param $count Int number of shares that will be sold
     * @return bool True if the sale was allowed, false otherwise
     */
    public static function sellShares($user, $stockId, $count) {
        $stock = self::findMatchingStock($user, $stockId);
        $ownedShares = $stock->share_count;
        if($ownedShares > 0 && $ownedShares >= $count) {
            $amount = self::calcTotalStockValue($stock, $count);
            if(self::performTransaction($user, $amount)) {
                // Transaction approved and executed
                $stock->share_count -= $count;
                $stock->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the total stock value for a given stock. Pulls the
     * most recent price of the stock from the FinanceAPI class.
     *
     * @param $stock Stock
     * @param $count Int number of stocks
     * @return float|int Total value of the stocks
     */
    public static function calcTotalStockValue($stock, $count) {
        $data = FinanceAPI::getAllStockInfo([$stock['ticker_symbol']])['data'][0];
        $currency = $data['currency'];
        $price = $data['price'];
        if($currency !== 'USD') {
            $price = CurrencyConverter::convertToUSD($currency, $price);
        }
        return $price * $count;
    }

    /**
     * Finds the matching stock according to its id.
     *
     * @param $user User
     * @param $stockId Int id of the stock to find
     * @return null if the stock is not found, Portfolio_Stock otherwise
     */
    public static function findMatchingStock($user, $stockId) {
        $stocks = $user->portfolios->portfolio_stocks;
        foreach($stocks as $stock) {
            if($stock->id - 1 == $stockId) {
                // id - 1 to compensate for SQL index starting at 1
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

}
