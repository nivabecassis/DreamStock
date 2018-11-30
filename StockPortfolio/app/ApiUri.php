<?php

namespace App;

// Define currency exchange api link as CURRENCYEXCHANGE constant
if (!defined('CURRENCYEXCHANGE')) { // Check if constant exists already
    define("CURRENCYEXCHANGE", "https://www.freeforexapi.com/api/live?pairs=");
}

if (!defined('WORLDTRADINGDATA')) {
    define("WORLDTRADINGDATA", "https://www.worldtradingdata.com/api/v1/stock");
}

if (!defined('APIKEY')) {
    define("APIKEY", "mciLyGJgY1OsX0JXQOhRsG45bziDeiqG00eKT7ucc5bP3Gj21eWuT9GTC7Jw");
}
