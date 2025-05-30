<?php

namespace Techquity\AeroCustomer2FA\AccountArea\Pages;

use Aero\AccountArea\AccountAreaPage;
use Aerocargo\Customer2FA\Http\Middleware\EnsureRequestHasChallengedUser;
use Techquity\AeroCustomer2FA\AccountArea\Forms\VerifyEmailAuthenticationForm;
use Techquity\AeroCustomer2FA\AccountArea\Forms\VerifySmsAuthenticationForm;
use Techquity\AeroCustomer2FA\AccountArea\Steps\AttachCustomer;

class VerifySmsAuthenticationPage extends AccountAreaPage
{
    protected static $steps = [
        AttachCustomer::class,
    ];

    protected static $middleware = [
        'account.guest',
        EnsureRequestHasChallengedUser::class,
    ];

    protected $sections = [
        'page-heading' => 'account-area::partials.page-header',
        'alert' => 'account-area::components.alert',
        'verify-two-factor-authentication-form' => VerifySmsAuthenticationForm::class,
    ];

    static function title(): string
    {
        return __('customer-2fa::titles.two_factor_authentication');
    }

    static function route(): string
    {
        return 'verify-two-factor-authentication/sms';
    }

    static function routeName(): string
    {
        return 'account.verify-two-factor-authentication-sms';
    }
}
