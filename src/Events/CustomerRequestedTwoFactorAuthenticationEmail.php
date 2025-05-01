<?php

namespace Techquity\AeroCustomer2Fa\Events;

use Aero\Account\Models\Customer;
use Aero\Events\ManagedEvent;

class CustomerRequestedTwoFactorAuthenticationEmail extends ManagedEvent
{
    public static $previewable = true;

    public $customer;

    public $customerName;
    public $customerEmail;

    public $code;

    public static $variables = [
        'customer',
        'customerName',
        'customerEmail',
        'code',
    ];

    public function __construct(Customer $customer, string $code)
    {
        $this->customer = $customer;

        $this->customerName = $customer->name;
        $this->customerEmail = $customer->email;

        $this->code = $code;
    }

    public function getNotifiable()
    {
        return parent::getNotifiable() ?: $this->customer;
    }
}
