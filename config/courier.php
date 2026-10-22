<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Courier Provider
    |--------------------------------------------------------------------------
    |
    | The default courier service used for creating parcel consignments.
    | Supported drivers: 'steadfast', 'pathao'.
    |
    */
    'default' => env('COURIER_DEFAULT_DRIVER', 'steadfast'),

    /*
    |--------------------------------------------------------------------------
    | Steadfast Courier Service
    |--------------------------------------------------------------------------
    |
    | Official API credentials and base URL for Steadfast Courier.
    | API documentation: https://portal.steadfast.com.bd
    |
    */
    'steadfast' => [
        'api_key' => env('STEADFAST_API_KEY'),
        'secret_key' => env('STEADFAST_SECRET_KEY'),
        'base_url' => env('STEADFAST_BASE_URL', 'https://portal.steadfast.com.bd/api/v1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pathao Courier Service
    |--------------------------------------------------------------------------
    |
    | Official API credentials and base URL for Pathao Merchant API.
    | API documentation: https://hermes-api-sandbox.pathao.com
    |
    */
    'pathao' => [
        'client_id' => env('PATHAO_CLIENT_ID'),
        'client_secret' => env('PATHAO_CLIENT_SECRET'),
        'username' => env('PATHAO_USERNAME'),
        'password' => env('PATHAO_PASSWORD'),
        'store_id' => env('PATHAO_STORE_ID', 1),
        'base_url' => env('PATHAO_BASE_URL', 'https://hermes-api-sandbox.pathao.com'),
        'webhook_secret' => env('PATHAO_WEBHOOK_SECRET'),
    ],
];
