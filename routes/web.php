<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroCustomer2Fa\Http\Controllers\Customer2faController;

Route::controller(Customer2faController::class)
    ->prefix('/customer-2fa')
    ->name('customer-2fa.')
    ->group(function () {
        Route::post('/update', 'update')->name('update');
        Route::get('/send', 'send')->name('send');
    });
