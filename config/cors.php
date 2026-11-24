<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */
    
    'paths' => [
        'api/*',
        'spa/*',
        'sanctum/csrf-cookie',
        'paypal/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://ilmohikmat.org',
        'https://control.ilmohikmat.org',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'X-Requested-With',
        'Content-Type',
        'X-Guest-Token',
        'X-XSRF-TOKEN',
        'Authorization',
        'Accept',
    ],

    'exposed_headers' => [],

    'max_age' => 7200,           // 2 hours cache for preflight

    'supports_credentials' => true,
];
