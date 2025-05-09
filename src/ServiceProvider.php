<?php

namespace Techquity\AeroCustomer2Fa;

use Aero\Account\Events\CustomerRegistered;
use Aero\Account\Models\Customer;
use Aero\AccountArea\AccountArea;
use Aero\AccountArea\Http\Requests\ValidateAccountDetails;
use Aero\AccountArea\Http\Requests\ValidateRegister;
use Aero\AccountArea\Http\Responses\AccountDetailsSet;
use Aero\AccountArea\Http\Responses\AccountRegisterSet;
use Aero\Common\Facades\Settings;
use Aero\Common\Providers\ModuleServiceProvider;
use Aero\Common\Settings\SettingGroup;
use Aero\Events\ManagedHandler;
use Aerocargo\Customer2FA\Actions\Enable2fa;
use Aerocargo\Customer2FA\Facades\Customer2FA;
use Aerocargo\Customer2FA\Models\Customer2faMethod;
use Illuminate\Routing\Router;
use Techquity\AeroCustomer2Fa\AccountArea\Forms\VerifyEmailAuthenticationForm;
use Techquity\AeroCustomer2Fa\AccountArea\Forms\VerifySmsAuthenticationForm;
use Techquity\AeroCustomer2Fa\AccountArea\Pages\VerifyEmailAuthenticationPage;
use Techquity\AeroCustomer2Fa\AccountArea\Pages\VerifySmsAuthenticationPage;
use Techquity\AeroCustomer2Fa\Console\Commands\FixEncryptionKeysCommand;
use Techquity\AeroCustomer2Fa\Drivers\EmailAuthenticationDriver;
use Techquity\AeroCustomer2Fa\Drivers\SmsAuthenticationDriver;
use Techquity\AeroCustomer2Fa\Events\CustomerRequestedTwoFactorAuthenticationEmail;

class ServiceProvider extends ModuleServiceProvider
{
    protected $listen = [
        CustomerRequestedTwoFactorAuthenticationEmail::class => [
            ManagedHandler::class,
        ]
    ];

    public function setup(): void
    {
        Settings::group('customer-2fa', function (SettingGroup $group) {
            $group->string('sms-message')
                ->hint('Use {{ code }} to display the SMS code')
                ->default('Your Verification Code is {{ code }}')
                ->max(160)
                ->section('SMS');

            $group->eloquent('default-auth-method', Customer2faMethod::class);
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        $this->addMobileFieldToRegistrationForm();
        $this->publishFiles();

        $this->publishes([
            __DIR__ . '/../config/two-factor-authentication.php' => config_path('two-factor-authentication.php')
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/two-factor-authentication.php', 'two-factor-authentication');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'customer-2fa');

        Router::addStoreRoutes(__DIR__ . '/../routes/web.php');

        $this->commands([
            FixEncryptionKeysCommand::class,
        ]);

        Customer2FA::registerDrivers([
            EmailAuthenticationDriver::NAME => EmailAuthenticationDriver::class,
            SmsAuthenticationDriver::NAME => SmsAuthenticationDriver::class,
        ]);

        AccountRegisterSet::extend(function($builder) {
            $customer = $builder->getData('user');
            $method = setting('customer-2fa.default-auth-method');

            $customer->customer_2fa_telephone_number = $builder->request->get('mobile');
            $customer->save();

            if ($method) {
                $customer->two_factor_authentication_method_id = $method->id;
                $customer->save();

                $auth = new Enable2fa;
                $auth($customer);

                $customer->two_factor_authentication_driver->enable();
            }
        });

        $validationRules = ['mobile' => 'required', 'numeric', 'digits_between:9,12'];
        ValidateAccountDetails::expects('mobile', $validationRules);

        AccountDetailsSet::extend(function($builder){
            if ($mobile = $builder->request->get('mobile')){

                $customer = Customer::find($builder->getData('user')->id);
                $customer->customer_2fa_telephone_number = $mobile;

                if ($customer->isDirty('customer_2fa_telephone_number')) {
                    $customer->save();
                }

            }
        });

        AccountArea::registerPage(VerifyEmailAuthenticationPage::class);
        AccountArea::registerForm(VerifyEmailAuthenticationForm::class);

        AccountArea::registerPage(VerifySmsAuthenticationPage::class);
        AccountArea::registerForm(VerifySmsAuthenticationForm::class);

        Customer::macro('getEmailCensoredAttribute', function () {
            list($username, $domain) = explode('@', $this->email);

            $length = strlen($username);
            $visible = 5;

            $times = $length - $visible;
            if ($times < 0) {
                $times = 0;
            }

            $censored = str_repeat('*', $times);

            return substr($username, 0, $visible) . $censored . '@' . $domain;
        });

        Customer::macro('getPhoneCensoredAttribute', function () {
            $address = $this->addresses->first();

            if ($address || $this->customer_2fa_telephone_number) {
                $phone = $this->customer_2fa_telephone_number ?? $address->mobile;
                if ($phone) {
                    $start = substr($phone, 0, 3);
                    $end = substr($phone, -3, 3);
                    $censored = strlen($phone) - 3 - 3;

                    if ($censored < 0) {
                        $censored = 0;
                    }

                    return $start . str_repeat('*', $censored) . $end;
                }
            }

            return '';
        });

    }

    protected function addMobileFieldToRegistrationForm(): void
    {
        Customer::makeFillable('customer_2fa_telephone_number');
        $validationRules = ['mobile' => ['required', 'digits_between:9,12']];
        ValidateRegister::expects('mobile', $validationRules);
    }

    protected function publishFiles()
    {
        $this->publishes([
            __DIR__.'/../resources/views/account-area' => base_path("themes/" . config('two-factor-authentication.theme') . "/resources/views/vendor/account-area/sections"),
        ], 'aero-customer-2fa');
    }

}
