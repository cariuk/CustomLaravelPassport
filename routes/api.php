<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get("token", "Api\TokenController@createPassportTokenByClient");

Route::group(['middleware' => 'client'],function() {
    Route::post("token/login", "Api\TokenController@createPassportTokenByUser");
    Route::post("token/login/personal", "Api\TokenController@createPassportTokenPersonalByUser");
    Route::get("getdata", "Api\DataController@index");
});

Route::group(['middleware' => 'auth:api'],function() {
    Route::get("getuser", "Api\DataController@user");
});
