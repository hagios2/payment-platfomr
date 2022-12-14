<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::resource('payment-method', PaymentMethodController::class);

Route::get('login', [AuthController::class, 'showForm'])->name('login.form');

Route::post('login', [AuthController::class, 'login'])->name('login');

Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

Route::get('transactions', [PaymentController::class, 'index'])->name('transactions');

Route::post('make-payment', [PaymentController::class, 'initStripePayment'])->name('add.charge');

Route::post('add/card-details', [PaymentController::class, 'addCardDetails'])->name('add.card');

Route::post('refund/{transaction}/charge', [PaymentController::class, 'refundStripePayment'])->name('refund.charge');
