<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "log", "greenweb", "reve", "alpha"
    |
    */
    'default' => env('SMS_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        'log' => [
            'channel' => env('LOG_CHANNEL', 'stack'),
        ],

        // Bangladeshi SMS Gateways (extensible)
        'greenweb' => [
            'api_token' => env('GREENWEB_SMS_TOKEN'),
        ],

        'reve' => [
            'api_key' => env('REVE_SMS_KEY'),
            'secret' => env('REVE_SMS_SECRET'),
            'caller_id' => env('REVE_SMS_CALLER_ID'),
        ],

        'alpha' => [
            'api_key' => env('ALPHA_SMS_KEY'),
        ],
    ],
];
