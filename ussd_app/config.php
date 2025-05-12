<?php

return [
    // Africa's Talking Configuration
    'africastalking' => [
        'username' => env('AFRICAS_TALKING_USERNAME'),
        'api_key' => env('AFRICAS_TALKING_API_KEY'),
        'service_code' => env('USSD_SERVICE_CODE'),
    ],

    // Database Configuration
    'database' => [
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'finance_tracker'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],

    // Application Settings
    'settings' => [
        'currency' => 'KES',
        'default_language' => 'en',
        'max_menu_items' => 10,
    ],
];
