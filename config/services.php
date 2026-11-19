<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'google_vision' => [
        'api_key' => env('GOOGLE_CLOUD_VISION_API_KEY'),
        'endpoint' => env('GOOGLE_CLOUD_VISION_ENDPOINT', 'https://vision.googleapis.com/v1/images:annotate'),
    ],

    'fonnte' => [
        'token' => env('FONNTE_API_TOKEN'),
        'endpoint' => env('FONNTE_API_ENDPOINT', 'https://api.fonnte.com/send'),
    ],

    'rsa' => [
        'private_key_path' => env('RSA_PRIVATE_KEY_PATH', storage_path('app/keys/rsa_private_2048.pem')),
        'public_key_path' => env('RSA_PUBLIC_KEY_PATH', storage_path('app/keys/rsa_public_2048.pem')),
    ],

    'geofence' => [
        'center_lat' => (float) env('GEOFENCE_CENTER_LAT', -4.007468),
        'center_lng' => (float) env('GEOFENCE_CENTER_LNG', 119.661131),
        'radius_meters' => (float) env('GEOFENCE_RADIUS_METERS', 700.0),
    ],

];
