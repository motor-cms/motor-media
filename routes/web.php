<?php

Route::group([
    'as'         => 'backend.',
    'prefix'     => 'backend',
    'namespace'  => 'Motor\Media\Http\Controllers\Backend',
    'middleware' => [
        'web',
        'web_auth',
        'navigation',
    ],
], static function () {
    if (config('motor-media.routes.files')) {
        Route::resource('files', 'FilesController');
    }
});
