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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Request Briva Online Bank BRI
 */
Route::post('/request-inquery','InqueryController@requestInquery');
Route::post('/request-payment','PaymentController@requestPayment');

/**
 * Session
 */

Route::post('/login','UserController@login');