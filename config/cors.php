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
    // قانون same policy میگوید که فقط درخواست هایی که از یک منبع باشند اجرا میشود مثلا نباید پورت یا پروتکل یا زیر دامنه یا دامنه دیگری باشد
    // برای حل این مشکل از CORS استفاده میشود که در request یک هدر با نام origin و مقدار سایتی که درخواست میدهد قرار میگیرد و در response یک هدر با نام Access-control-Allow-Origin و مقدار سایت هایی که اجازه دسترسی دارند قرار دارد که درصورت یکی بودن این دو هدر درخواست اجرا میشود و بلاک نمیشود

    // در صورت نیاز میتوان از پکیج laravel cors هم استفاده کرد که امکانات بیشتری در اختیار ما قرار میدهد

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // در اینجا گفته شده که فقط برای مسیر هایی که با api شروع میشوند دستورات زیر را در نظر بگیرد

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // در اینجا میتوان مشخص کرد که کدام سایت ها میتوانند دسترسی داشته باشند مثلا به صورت زیر
    // 'allowed_origins' => ['https://webprog.io', 'https://www.test-cors.org'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
