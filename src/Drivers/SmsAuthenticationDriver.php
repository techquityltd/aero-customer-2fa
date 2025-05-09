<?php

namespace Techquity\AeroCustomer2Fa\Drivers;

use Exception;
use Illuminate\Support\Facades\Log;
use Techquity\AeroCustomer2Fa\AccountArea\Pages\VerifyEmailAuthenticationPage;
use Techquity\AeroCustomer2Fa\AccountArea\Pages\VerifySmsAuthenticationPage;
use Techquity\AeroCustomer2Fa\Helpers\TwoFactorLog;

class SmsAuthenticationDriver extends AuthenticationDriver
{
    public const NAME = 'sms';

    protected function sendNotification ($customer): void
    {
        $code = $this->getCode($customer);

        try {
            $address = $customer->addresses->first();
            $number = $customer->customer_2fa_telephone_number ?: $address->mobile;

            $message = str_replace('{{ code }}', $code, setting('customer-2fa.sms-message'));

            $class = config('two-factor-authentication.sms-class');
            $sms = new $class();
            $sms::send($number, $message);
        } catch (Exception $e) {
            TwoFactorLog::error($e->getMessage());
        }
    }

    protected function getPage(): string
    {
        return VerifySmsAuthenticationPage::url();
    }
}
