<?php

return [
    'base_url' => rtrim(env('CLICKPESA_BASE_URL', 'https://api.clickpesa.com/third-parties'), '/'),
    'client_id' => env('CLICKPESA_CLIENT_ID'),
    'api_key' => env('CLICKPESA_API_KEY'),
    'checksum_key' => env('CLICKPESA_CHECKSUM_KEY'),
    'use_checksum' => env('CLICKPESA_USE_CHECKSUM', false),
    'verify_ssl' => env('CLICKPESA_VERIFY_SSL', env('APP_ENV') !== 'local'),
    'min_amount' => env('CLICKPESA_MIN_AMOUNT', 500),
    'max_amount' => env('CLICKPESA_MAX_AMOUNT', 3000000),
];
