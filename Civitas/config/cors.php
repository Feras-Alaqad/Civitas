<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The application is server-rendered and does not expose a public cross-origin
    | API. Webhook endpoints receive server-to-server requests, which are not
    | subject to CORS preflights. Cross-origin browser requests are therefore
    | rejected by keeping the allowed origins list empty.
    |
    */

    'paths' => ['api/*', 'webhooks/*'],

    'allowed_methods' => ['POST'],

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Nowpayments-Sig',
        'X-Lahza-Signature',
        'X-CSRF-TOKEN',
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];