<?php

namespace App\Http\Controllers;

use App\User;

/*
 * Portfolio controller
 *
 * @author Austin Antoine
 * @author Stephen Kwan
 * @author Niv Abecassis
 * @author Yehoshua Fish
 */
class PortfolioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user balance
     *
     * @param $username User to get balance for
     * @return Response
     */
    public function getBalance($username)
    {
        // Not sure if this works properly yet
        $portfolio = User::find($username)->portfolios();
        return $portfolio->cash_owned;
    } // getBalance()
}
