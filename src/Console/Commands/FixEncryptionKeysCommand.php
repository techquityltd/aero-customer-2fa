<?php

namespace Techquity\AeroCustomer2Fa\Console\Commands;

use Aero\Account\Models\Customer;
use Aerocargo\Customer2FA\RecoveryCode;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixEncryptionKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '2fa:fix:encryption-keys {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customer = Customer::firstWhere('email', $this->argument('email'));

        if ($customer) {
            $customer->forceFill([
                'two_factor_secret' => encrypt($customer->two_factor_authentication_driver->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all())),
            ])->save();

            $this->info('fixed');
        } else {
            $this->error('customer not found');
        }

    }
}