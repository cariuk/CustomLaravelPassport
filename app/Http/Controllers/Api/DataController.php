<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;

class DataController extends Controller{

    function index(Request $request){
//        $jwtParse = new Parser();
//        $jwtParse->parse(str_replace("Bearer ","",$request->header("Authorization")))->getClaims();
        return response()->json([
            "status" => 00,
            "message" => "sukses get abjad",
            "data" => [
                "a","b","c"
            ]
        ]);
    }

    function user(Request $request){
        return response()->json([
            "status" => 00,
            "message" => "sukses get data user",
            "data" => Auth::user()
        ]);
    }
}
