<?php
return [
    'api_endpoint' => getenv('YAPE_API_ENDPOINT'),
    'api_key' => getenv('YAPE_API_KEY'),
    'merchant_id' => getenv('YAPE_MERCHANT_ID'),
    'callback_url' => getenv('APP_URL') . '/api/yape/callback',
    'currency' => 'PEN',
    'timeout' => 300, // 5 minutos para expiración de pago
    'min_amount' => 1.00,
    'max_amount' => 500.00,
    'test_mode' => getenv('APP_ENV') !== 'production'
];