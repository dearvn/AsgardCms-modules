<?php

return [
    'name' => 'Otp',
    'otp_service_enabled' => true,
    'otp_default_service' => env("OTP_SERVICE", "nexmo"),
    'services' => [
        'biotekno' => [
            "class" => \Modules\Otp\Services\BioTekno::class,
            "username" => env('OTP_USERNAME', null),
            "password" => env('OTP_PASSWORD', null),
            "transmission_id" => env('OTP_TRANSMISSION_ID', null)
        ],
        'nexmo' => [
            'class' => \Modules\Otp\Services\Nexmo::class,
            'api_key' => env("OTP_API_KEY", null),
            'api_secret' => env('OTP_API_SECRET', null),
            'from' => env('OTP_FROM', null)
        ],
        'twilio' => [
            'class' => \Modules\Otp\Services\Twilio::class,
            'account_sid' => env("TWILIO_SID", null),
            'auth_token' => env("TWILIO_AUTH_TOKEN", null),
            'from' => env("OTP_FROM", '+12345678901')
        ]
    ],
    'user_phone_field' => 'phone',
    'otp_reference_number_length' => 6,
    'otp_timeout' => 120,
    'otp_digit_length' => 6,
    'encode_password' => true
];
