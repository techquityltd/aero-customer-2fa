# Techquity Customer 2FA

Extension for aerocargo/customer-2fa

## Installation

`composer require techquity/aero-customer-2fa`

## Setup

### Email

- In Admin > Modules > Customer 2FA create a new Authentication Method with the driver set to 'email'
- In Admin > Configuration > Mail Notifications create a new Mail Notification listening to the `Customer Requested Two Factor Authentication Email` event

### SMS
- Run `php artisan vendor:publish` and select `Techquity\AeroCustomer2Fa\ServiceProvider`. 
- Then in config/two-factor-authentication.php set `sms-class`to one of the below values, only some modules are currently supported

| Module                   | Key                               |   
|--------------------------|-----------------------------------|
| techquity/aero-textlocal | Techquity\AeroTextlocal\TextLocal |

- Then in Admin > Modules > Customer 2FA create a new Authentication Method with the driver set to 'sms'
- In Admin > Configuration > Settings change the SMS Message setting to the text you want to send
- Make sure to install and configure the module according to its instructions

## Extending

### Creating a new Driver

- Create a new class that extends `Techquity\AeroCustomer2Fa\Drivers\AuthenticationDriver`
- Set `public const NAME = '';` to the key for your driver
- Fill in the `sendNotification($customer)`, you can get the code from `$this->getCode($customer)` method
- Add the below to a service provider:¡
```
Customer2FA::registerDriver(
    YourAuthenticationDriver::NAME,
    YourAuthenticationDriver::class
);
```

### Adding a new SMS Class

You need to create a class with a public static method matching this format `send($number, $message): bool` 
that returns `true` on success and `false` on fail