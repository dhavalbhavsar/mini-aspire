<?php

use Illuminate\Http\Request;
use Modules\Loan\Http\Controllers\LoanController;

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

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('v1/loan/')
    ->controller(LoanController::class)
    ->group(function () {
        Route::get('list', 'index')->can('loan_list');
        Route::post('create', 'store')->can('loan_create');
        Route::post('payment/{id}', 'payment')->can('loan_repay');
        Route::post('approve/{loan}', 'approve')->can('loan_approve');
        Route::get('{id}', 'detail')->can('loan_view');
    });