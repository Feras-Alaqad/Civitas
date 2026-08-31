<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lahza (Bank of Palestine) Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials and settings for the Lahza
    | payment gateway. Values come from the Lahza dashboard.
    |
    */

    'public_key' => env('LAHZA_PUBLIC_KEY'),

    'secret_key' => env('LAHZA_SECRET_KEY'),

    'webhook_secret' => env('LAHZA_WEBHOOK_SECRET'),

    'callback_url' => env('LAHZA_CALLBACK_URL'),

    'base_url' => env('LAHZA_BASE_URL', 'https://api.lahza.io'),

    'default_currency' => env('LAHZA_DEFAULT_CURRENCY', 'ILS'),

];
