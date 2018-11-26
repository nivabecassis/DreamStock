<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

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
//        $data = [
//            'user' => \Auth::user(),
//            'portfolio' => \Auth::user()->portfolios(),
//            'stocks' => \Auth::user()->portfolios()->portfolio_stocks(),
//        ];
//        return view('home', $data);
        return \Auth::user()->portfolios();
    }
}
