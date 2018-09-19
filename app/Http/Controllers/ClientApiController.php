<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ClientApiController extends Controller{
    function index(Request $request){
        try{
            $client = new Client();
            $client = $client->request(
                'GET',
                url('api/token'), [
                    'headers' =>  [
                        "Accept" => "application/json",
                        "clientId" => 2,
                        "clientSecret" => "GGzs1aL0PEUnE8XPenNVnHL2UqKZIeSeulJ5vFlL"
                    ]
                ]
            );
            $getResponse = json_decode($client->getBody());
            return response()->json($getResponse);

        }catch (\Exception $exception){
            return response()->json([
                "message" => $exception->getMessage()
            ]);
        }
    }
}
