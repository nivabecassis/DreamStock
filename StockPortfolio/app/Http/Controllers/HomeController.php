<?php

namespace App\Http\Controllers;

use Auth;

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
        $user = Auth::user();
        $name = $user->name;
        $cash = $user->portfolios->cash_owned;
        $since = $user->timestamps;

        return view('home', [
            'user' => $user,
            'name' => $name,
            'cash' => $cash,
            'since' => $since,
        ]);
    }
}
