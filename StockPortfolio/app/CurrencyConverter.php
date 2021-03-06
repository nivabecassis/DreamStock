<?php

namespace App;

use App\ApiUri;

/**
 * CurrencyConverter is a utility class responsible for converter any type
 * of currency into USD.
 */
class CurrencyConverter
{
    /**
     * Converts any type of currency that 'FreeForexAPI' supports to USD
     *
     * @param $currency Original currency (e.g 'CAD', 'JPY, 'EUR', ...)
     * @param $value Original value (e.g '9', '9.0', '99.00', ...)
     * @return $value under USD currency
     */
    public static function convertToUSD($currency, $value)
    {
        $pairs = "USD" . $currency;
        $json = file_get_contents(CURRENCYEXCHANGE . $pairs);
        $data = json_decode($json);
        $rate = $data->rates->$pairs->rate;

        return $value / $rate;
    }
}
