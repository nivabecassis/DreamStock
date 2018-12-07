<?php

namespace App;

// Define currency exchange api link as CURRENCYEXCHANGE constant
define("CURRENCYEXCHANGE", "https://www.freeforexapi.com/api/live?pairs=");

/**
 * Define world trading data api link as WOLRDTRADINGDATA constant
 * We chose World Trading Data over Alpha Vantage as our API primarily because
 * we thought having 250 calls a day would be better than 5 a minute and also
 * because the JSON returned by World Trading Data seemed like it had everything
 * we needed and would be easy to work with
 */
define("WORLDTRADINGDATA", "https://www.worldtradingdata.com/api/v1/stock");

// Define worldtradingdata api token as APIKEY constant
//define("APIKEY", "mciLyGJgY1OsX0JXQOhRsG45bziDeiqG00eKT7ucc5bP3Gj21eWuT9GTC7Jw");

// Spare key if we run out of API calls for the day
// define ("APIKEY", "54nCAF0E1giPTW0moWVUQe4Ts0qV6mVjCzHxuUTb4Xu3Ul486m8WYSilxej2");

define ("APIKEY", "GIw8eYl60EyMYJJE0h5nG7Xa1mScAVdeABdsSb9mKtppw9claparThBaAu4H");


