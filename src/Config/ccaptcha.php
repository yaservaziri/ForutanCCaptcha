<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Expiration
    |--------------------------------------------------------------------------
    |
    | Determines how long (in seconds) a generated captcha remains valid
    | before it expires and requires regeneration.
    |
    */
    'expire_seconds' => 30,

    /*
    |--------------------------------------------------------------------------
    | Attempt & Blocking Policy
    |--------------------------------------------------------------------------
    |
    | - max_attempts: number of failed verifications allowed per session
    | - block_duration_minutes: user is blocked for this many minutes after
    |   exceeding max_attempts.
    |
    */
    'max_attempts' => 5,
    'block_duration_minutes' => 60,

    /*
    |--------------------------------------------------------------------------
    | Routing & Middleware
    |--------------------------------------------------------------------------
    |
    | The prefix defines the base URL for captcha routes.
    | The middleware stack defines what should run before showing captcha.
    |
    */
    'route_prefix' => 'ccaptcha',
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | These throttle settings prevent abuse or spam requests
    | for both showing and verifying captcha.
    | Format: "<max_attempts>,<minutes>"
    |
    */
    'throttle' => [
        'show' => '60,1',
        'verify' => '20,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Define where users should be redirected after successfully passing
    | the captcha challenge. You can specify per-context redirects if needed.
    |
    */
    'redirect_on_pass' => [
        'default' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Settings
    |--------------------------------------------------------------------------
    |
    | Default image processing configuration used by the
    | `ccaptcha:prepare-images` command and the controller.
    |
    | - image_width / image_height define final image dimensions
    | - image_quality controls compression level (1–100)
    | - storage_path defines where prepared captcha images are stored
    |
    */
    'image_width' => 500,
    'image_height' => 400,
    'image_quality' => 90,
    'storage_path' => storage_path('app/private/ccaptcha'),
    'radius_min' => 20,
    'radius_max' => 35,
    'min_circle_count' => 10,
    'max_circle_count' => 15
];
