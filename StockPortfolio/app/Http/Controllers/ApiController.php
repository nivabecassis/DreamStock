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

    /**
     * Provides an array of all user associated stocks.
     * If the user does not own any stocks, an empty array
     * is returned.
     *
     * Method type: GET
     * Success response: 200
     * Error response: 401
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllStocks(Request $request) {
        return response()->json('{"data":"hello"}');
    }
}
