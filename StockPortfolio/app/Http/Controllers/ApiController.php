<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function buyStock(Request $request) {

        $user = auth('api')->user(); //returns null if not valid
        if (!$user)
        {
            return response()->json(['error' => 'invalid_token'], 401);
        }
    }
}
