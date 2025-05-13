<?php

namespace Techquity\AeroCustomer2FA\AccountArea\Steps;

use Aero\Account\Models\Customer;
use Aero\Responses\ResponseBuilder;
use Aero\Responses\ResponseStep;
use Closure;

class AttachCustomer implements ResponseStep
{
    public function handle(ResponseBuilder $builder, Closure $next)
    {
        $customer = Customer::query()
            ->find(
                request()->session()->get('login.id')
            );

        $builder->setData('customer', $customer);
    }
}
