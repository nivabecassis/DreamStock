<?php

namespace App\Http\Controllers;

use App\FinanceAPI;
use App\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function buyStock(Request $request)
    {

        $stockInfo = FinanceAPI::getAllStockInfo(explode(",", $request->input("ticker")));
        $user = auth('api')->user(); //returns null if not valid

        if (!$user)
        {
            return response()->json(['error' => 'invalid_token'], 401);
        }

        elseif (!UserUtility::canBuyShares($user, $stockInfo, $request->input("quantity")))
        {
            return response()->json(['error' => 'insufficient cash'], 403);
        }

        elseif (!is_numeric($request->input("quantity")) /* || is_numeric($request->input("ticker"))*/)
        {
            return response()->json(['error' => "invalid ticker or quantity"], 400);
        }

        else
        {
            $user = auth('api')->user();
            UserUtility::buyStock($user, $stockInfo, $request->input("ticker"), $request->input("quantity"));
            return response()->json(['cashleft' => $user->portfolios->cash_owned], 200);
        }
    }
}
