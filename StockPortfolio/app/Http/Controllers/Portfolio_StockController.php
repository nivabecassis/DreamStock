<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Portfolio_StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
}
