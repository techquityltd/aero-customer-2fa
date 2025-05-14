<?php

namespace Techquity\AeroCustomer2FA\Drivers;

use Aero\Account\Models\Customer;
use Aerocargo\Customer2FA\AccountArea\Pages\DisableTwoFactorAuthenticationPage;
use Aerocargo\Customer2FA\AccountArea\Pages\VerifyTwoFactorAuthenticationPage;
use Aerocargo\Customer2FA\Contracts\TwoFactorAuthenticationDriver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class AuthenticationDriver implements TwoFactorAuthenticationDriver
{
    public function generateSecretKey(): string
    {
        return uniqid();
    }

    public function enable()
    {
        $customer = Auth::guard(config('aero.account.auth.defaults.guard'))->user();

        $customer->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        $customer->forget();

        return redirect()->back()->with([
            'message' => __('customer-2fa::alerts.two_factor_authentication_enabled'),
        ]);
    }

    public function verify()
    {
        $customer = Customer::query()
            ->find(
                request()->session()->get('login.id')
            );

        $this->sendNotification($customer);

        $url = $this->getPage();

        return redirect($url);
    }

    public function disable()
    {
        return redirect(DisableTwoFactorAuthenticationPage::url());
    }

    public function verifyCode(string $secret, string $code): bool
    {
        $sentCode = Cache::get($secret);

        return $sentCode === $code;
    }

    protected function getCode($customer): string
    {
        Cache::forget(
            $cacheKey = $customer->decrypted_two_factor_secret
        );

        return Cache::remember($cacheKey, 600, static function () {
            return (string) rand(100000, 999999);
        });
    }

    abstract protected function sendNotification($customer): void;

    abstract protected function getPage(): string;
}
