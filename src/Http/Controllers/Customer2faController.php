<?php

namespace Techquity\AeroCustomer2Fa\Http\Controllers;

use Aero\Account\Models\Customer;
use Aerocargo\Customer2FA\Actions\Enable2fa;
use Aerocargo\Customer2FA\Facades\Customer2FA;
use Aerocargo\Customer2FA\Models\Customer2faMethod;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Customer2faController extends Controller
{
    public function update(Request $request) {
        $methods = Customer2faMethod::all()->pluck('driver')->implode(',');

        $data = $request->validate([
            'two_factor_authentication' => "required|in:$methods",
        ]);

        $driver = $data['two_factor_authentication'];

        $method = Customer2faMethod::firstWhere('driver', $driver);

        /* @var Customer $customer */
        $customer = auth()->user();

        if ($method && $method->id !== $customer->two_factor_authentication_method_id) {
            $customer->two_factor_authentication_method_id = $method->id;
            $customer->save();

            $auth = new Enable2fa;
            $auth($customer);

            $customer->two_factor_authentication_driver->enable();
        }

        return redirect()->back();
    }

    public function send()
    {
        $customer = Customer::query()
            ->find(
                request()->session()->get('login.id')
            );

        $driver = match ($customer->twoFactorAuthenticationMethod->driver) {
            'sms' => 'email',
            'email' => 'sms',
        };

        $driverClass = Customer2FA::getDriver($driver);
        $driverClass->verify();

        return [
            'success' => true
        ];
    }
}
