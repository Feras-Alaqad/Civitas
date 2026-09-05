<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NOWPayments (Cryptocurrency) Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Credentials and settings for the NOWPayments cryptocurrency gateway.
    | Values come from the NOWPayments dashboard.
    |
    */

    'api_key' => env('NOWPAYMENTS_API_KEY'),

    'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),

    'sandbox' => env('NOWPAYMENTS_SANDBOX', true),

    'base_url' => env('NOWPAYMENTS_SANDBOX', true)
        ? 'https://api-sandbox.nowpayments.io/v1'
        : env('NOWPAYMENTS_BASE_URL', 'https://api.nowpayments.io/v1'),

    'default_pay_currency' => env('NOWPAYMENTS_DEFAULT_PAY_CURRENCY'),

    'ipn_callback_url' => env('NOWPAYMENTS_IPN_CALLBACK_URL'),

];
