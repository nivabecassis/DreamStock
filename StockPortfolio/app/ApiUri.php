<?php

namespace App;

// Define currency exchange api link as CURRENCYEXCHANGE constant
if (!defined('CURRENCYEXCHANGE')) { // Check if constant exists already
    define("CURRENCYEXCHANGE", "https://www.freeforexapi.com/api/live?pairs=");
}