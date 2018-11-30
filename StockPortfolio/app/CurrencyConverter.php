<?php

namespace App;

use App\ApiUri;

/**
 * Converts any type of currency that 'FreeForexAPI' supports to USD
 *
 * @param $currency Original currency (e.g 'CAD', 'JPY, 'EUR', ...)
 * @param $value Original value (e.g '9', '9.0', '99.00', ...)
 * @return $value under USD currency
 */
if (!function_exists('convertToUSD')) { // Checks if function is already defined
    function convertToUSD($currency, $value)
    {
        $pairs = "USD" . $currency;
        $json =  file_get_contents(CURRENCYEXCHANGE . $pairs);
        $data = json_decode($json);
        $rate = $data->rates->$pairs->rate;

        return $value / $rate;
    }
}
