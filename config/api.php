<?php

return [
    'version' => '1.0.0',

    'cms' => env('APP_CMS'),

    'max_limit' => 50,

    'cache' => [
        'enabled' => false,
        'minutes' => 10
    ],

    'login' => [
        'throttling' => true,
        'max_login_attemps' => 5,
        'lockout_time' => 60 * 5, // seconds
    ],

    'letter_avatar' => env('APP_LETTER_AVATAR'),

    /**
     * Use an absolute URL or a relative URL without a leading slash to use the current site's domain
     */

    'avatar' => env('APP_DEFAULT_AVATAR_URL'),

    'fieldoption_image_path' => env('APP_FIELDOPTION_IMAGE_PATH'),

    'thumbnails' => [
        'small' => [
            'width' => 160,
            'height' => 160
        ],

        'medium' => [
            'width' => 320,
            'height' => 320
        ],

        'large' => [
            'width' => 640,
            'height' => 640
        ]
    ]
];