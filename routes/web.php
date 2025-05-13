<?php

use Illuminate\Support\Facades\Route;
use Techquity\AeroCustomer2FA\Http\Controllers\Customer2faController;

Route::post('customer-2fa/update', 'Customer2faController@update')->prefix('/customer-2fa')->name('customer-2fa.update');