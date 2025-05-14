<?php

namespace Techquity\AeroCustomer2FA\Drivers;

use Techquity\AeroCustomer2FA\AccountArea\Pages\VerifyEmailAuthenticationPage;
use Techquity\AeroCustomer2FA\Events\CustomerRequestedTwoFactorAuthenticationEmail;

class EmailAuthenticationDriver extends AuthenticationDriver
{
    public const NAME = 'email';

    protected function sendNotification($customer): void
    {
        $code = $this->getCode($customer);

        event(new CustomerRequestedTwoFactorAuthenticationEmail($customer, $code));
    }

    protected function getPage(): string
    {
        return VerifyEmailAuthenticationPage::url();
    }
}
