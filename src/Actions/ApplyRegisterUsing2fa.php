<?php

namespace Techquity\AeroCustomer2FA\Actions;

use Aero\Account\Models\Customer;
use Aero\AccountArea\Http\Responses\Steps\ApplyRegister;
use Aero\Responses\ResponseBuilder;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApplyRegisterUsing2fa extends ApplyRegister
{
    /**
     * @throws ValidationException
     */
    public function handle(ResponseBuilder $builder, Closure $next)
    {
        $request = $builder->getParameter('request');

        dd($builder->getParameter('request'));

//        if ($user = $this->getTwoFactorAuthenticatableUser($request)) {
            return $this->sendTwoFactorChallengeResponse($request, $user);
//        }

        return parent::handle($builder, $next);
    }

    protected function getTwoFactorAuthenticatableUser(Request $request): ?Customer
    {
        if (is_numeric($unix = $request->cookie('customer_2fa_timestamp'))) {
            try {
                $timestamp = Carbon::parse((int) $unix);

                $days = setting('customer-2fa.two_factor_authentication_grace_period_days');

                if (now()->isBefore($timestamp->addDays($days))) {
                    return null;
                }
            } catch (\Exception $e) {}
        }

        /* @var $user Customer|null */
        $user = Customer::query()
            ->where('email', $request->input('email'))
            ->first();

        dd($user);

        if ($user === null) {
            return null;
        }

        if (! Auth::guard(config('aero.account.auth.defaults.guard'))->getProvider()->validateCredentials($user, ['password' => $request->input('password')])) {
            return null;
        }

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            return null;
        }

        return $user;
    }

    protected function sendTwoFactorChallengeResponse($request, $user)
    {
        $request->session()->put([
            'login.id' => $user->getKey(),
        ]);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('account.2fa.verify');
    }
}