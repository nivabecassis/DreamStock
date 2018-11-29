<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;

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
    public function getBalance()
    {
        // Not sure if this works properly yet
        return User::find(Auth::id())->portfolios()->cash_owned;
    } // getBalance()
}
