<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('home/transaction/{symbol}', 'HomeController@transaction')->name('transaction');
Route::post('/home/transaction/sell/{symbol}', 'HomeController@sell')->name('sell');

Route::get('/home', 'PortfolioController@index')->name('portfolio');
Route::post('/home', "Portfolio_StockController@quotes");
Route::post('/home/{symbol}', 'Portfolio_StockController@purchaseStock');
