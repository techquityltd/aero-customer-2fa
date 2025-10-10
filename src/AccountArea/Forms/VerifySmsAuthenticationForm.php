<?php

namespace Techquity\AeroCustomer2FA\AccountArea\Forms;

use Aero\AccountArea\AccountAreaForm;

class VerifySmsAuthenticationForm extends AccountAreaForm
{
    protected $class = 'bpa-enable-two-factor-authentication-form';

    protected $sections = [
        'verify_two_factor_authentication' => 'customer-2fa::sms-authentication',
    ];

    protected function method(): string
    {
        return 'post';
    }

    protected function route($data): string
    {
        return route('account.verify-two-factor-authentication');
    }
}
