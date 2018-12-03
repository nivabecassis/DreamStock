<?php

namespace App\Http\Controllers;

use App\ApiUri;
use App\FinanceAPI;
use App\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/*
 * Portfolio_Stock controller
 *
 * @author Austin Antoine
 */

class Portfolio_StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
}
