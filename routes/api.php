<?php

use Motor\Media\Http\Controllers\Api\FilesController;

Route::group([
    'middleware' => ['auth:sanctum', 'bindings', 'permission'],
    'prefix'     => 'api',
    'as'         => 'api.',
], static function () {
    Route::apiResource('files', FilesController::class);
});
