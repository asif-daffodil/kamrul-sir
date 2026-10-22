<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSLCommerz Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials assigned by SSLCommerz for API communication.
    | Default sandbox credentials for testing are 'testbox' / 'qwerty'.
    |
    */
    'store_id' => env('SSLCOMMERZ_STORE_ID', 'testbox'),
    'store_password' => env('SSLCOMMERZ_STORE_PASSWORD', 'qwerty'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | When true, requests are routed to sandbox.sslcommerz.com.
    | When false, requests are routed to securepay.sslcommerz.com.
    |
    */
    'sandbox' => env('SSLCOMMERZ_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | API Base URL & Endpoints
    |--------------------------------------------------------------------------
    |
    | Official SSLCommerz API endpoints for session initiation and server-to-server
    | order validation API.
    |
    */
    'api_domain' => env('SSLCOMMERZ_SANDBOX', true)
        ? 'https://sandbox.sslcommerz.com'
        : 'https://securepay.sslcommerz.com',

    'init_endpoint' => '/gwprocess/v4/api.php',
    'validation_endpoint' => '/validator/api/validationserverAPI.php',

    /*
    |--------------------------------------------------------------------------
    | Currency & Defaults
    |--------------------------------------------------------------------------
    */
    'currency' => env('APP_CURRENCY', 'BDT'),
];
