<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroCustomer2FA\Http\Controllers\Customer2faController;

Route::prefix('/customer-2fa')
    ->name('customer-2fa.')
    ->group(function () {
        Route::post('/update', [Customer2faController::class, 'update'])->name('update');
        Route::get('/send', [Customer2faController::class, 'send'])->name('send');
    });
