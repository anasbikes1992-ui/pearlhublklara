<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

    'xai' => [
        'key' => env('XAI_API_KEY'),
    ],

    'webxpay' => [
        'merchant_id' => env('WEBXPAY_MERCHANT_ID'),
        'api_key' => env('WEBXPAY_API_KEY'),
        'sandbox' => env('WEBXPAY_SANDBOX', true),
    ],

    'genie' => [
        'merchant_id' => env('GENIE_MERCHANT_ID'),
        'secret_key' => env('GENIE_SECRET_KEY'),
        'sandbox' => env('GENIE_SANDBOX', true),
    ],

    'koko_pay' => [
        'merchant_id' => env('KOKO_MERCHANT_ID'),
        'secret_key' => env('KOKO_SECRET_KEY'),
        'sandbox' => env('KOKO_SANDBOX', true),
    ],

    'mint_pay' => [
        'merchant_id' => env('MINT_MERCHANT_ID'),
        'secret_key' => env('MINT_SECRET_KEY'),
        'sandbox' => env('MINT_SANDBOX', true),
    ],

    'google_translate' => [
        'key' => env('GOOGLE_TRANSLATE_API_KEY'),
    ],

    'libretranslate' => [
        'url' => env('LIBRETRANSLATE_URL', 'https://libretranslate.com'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'deepgram' => [
        'key' => env('DEEPGRAM_API_KEY'),
    ],

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_ANON_KEY'),
        'service_key' => env('SUPABASE_SERVICE_KEY'),
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
    ],
];
