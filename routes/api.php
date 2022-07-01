<?php

use Motor\Media\Http\Controllers\Api\FilesController;

Route::group([
    'middleware' => ['auth:api', 'bindings', 'permission'],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'api',
    'as'         => 'api.',
], static function () {
    Route::apiResource('files', FilesController::class);
});

// TODO: is this still needed?
Route::group([
    'middleware' => ['web', 'web_auth', 'bindings', 'permission'],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'ajax',
    'as'         => 'ajax.',
], static function () {
    Route::get('files', [FilesController::class, 'index'])
         ->name('files.index');
    //Route::resource('files', 'FilesController');
});
