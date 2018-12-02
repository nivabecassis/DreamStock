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

}
