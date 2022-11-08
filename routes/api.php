<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('make-payment', [PaymentController::class, 'initStripePayment']);

Route::post('add/card-details', [PaymentController::class, 'addCardDetails']);

Route::post('refund/{transaction}/charge', [PaymentController::class, 'refundStripePayment']);
