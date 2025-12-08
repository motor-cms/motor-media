<?php

return [
    'models' => [
        'file' => Motor\Media\Models\File::class,
    ],
    'routes' => [
        'file' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Ops Token
    |--------------------------------------------------------------------------
    |
    | Secret token for accessing internal media operations via web routes.
    | Set MOTOR_MEDIA_OPS_TOKEN in your .env file to enable the routes.
    | Routes: /_m0ps/{token}/check and /_m0ps/{token}/sync
    |
    */
    'ops_token' => env('MOTOR_MEDIA_OPS_TOKEN'),
];
