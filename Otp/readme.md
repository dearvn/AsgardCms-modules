==========================
Thanks @tpaksu
==========================
Laravel OTP Login Module for AsgardCms
This package provides an One Time Password check step after successful login using the default authentication mechanism. The package stores all requested OTP and it's validation statuses in one_time_passwords and one_time_password_logs tables.

It uses the middleware included in this package to check if the user has passed the OTP check or not regarding the current authentication status.
==========================
## Requirements
Laravel 6.x
AsgardCMS 4.x

## Installation
Download and unzip to Modules folders in AsgardCMS

### Run commands:
```bash
 $ php artisan vendor:publish --provider="Modules\Otp\Providers\OtpServiceProvider"
```

```bash
 $ php artisan module:migrate Otp
```
### Add to .evn
```evn
TWILIO_SID=
TWILIO_AUTH_TOKEN=
TWILIO_VERIFY_SID=
OTP_SERVICE=twilio
OTP_FROM=
```
