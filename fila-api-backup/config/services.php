<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ollama' => [
        'url' => env('OLLAMA_API_URL', 'http://host.docker.internal:11434/api'),
        'model' => env('OLLAMA_MODEL', 'llama3:8b'),
        'circuit_breaker' => [
            'failure_threshold' => env('OLLAMA_CIRCUIT_BREAKER_THRESHOLD', 5),
            'reset_timeout' => env('OLLAMA_CIRCUIT_BREAKER_TIMEOUT', 60),
        ],
        'cache' => [
            'ttl' => env('OLLAMA_CACHE_TTL', 3600), // 1 hora
        ],
    ],

];
