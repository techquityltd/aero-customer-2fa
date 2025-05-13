<?php

namespace Techquity\AeroCustomer2FA\Helpers;

use Techquity\AeroLogs\AeroLog;

class TwoFactorLog extends AeroLog
{
    protected static ?string $key = '[TwoFactor]';
}
