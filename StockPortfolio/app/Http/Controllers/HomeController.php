<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\User;
use App\Portfolio;
use App\Portfolio_Stock;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Pass the current user's data to the view
        $portfolio = \Auth::user()->portfolios();
        $data = [
            'user' => \Auth::user(),
            'portfolio' => $portfolio->get(),
            'stocks' => $portfolio->portfolio_stocks()->get(),
        ];
        return var_dump($data);
    }
}
