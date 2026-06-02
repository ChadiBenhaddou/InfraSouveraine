<?php

return [
    'openai' => [
        'api_key' => env('AI_RECOMMENDER_API_KEY'),
        'endpoint' => env('AI_RECOMMENDER_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'model' => env('AI_RECOMMENDER_MODEL', 'gpt-4o-mini'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],
];
