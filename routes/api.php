<?php

Route::group([
    'middleware' => ['auth:api', 'bindings', 'permission'],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'api',
    'as'         => 'api.',
], static function () {
    Route::apiResource('files', 'FilesController');
});

// TODO: is this still needed?
Route::group([
    'middleware' => ['web', 'web_auth', 'bindings', 'permission'],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'ajax',
    'as'         => 'ajax.',
], static function () {
    Route::get('files', 'FilesController@index')
         ->name('files.index');
    //Route::resource('files', 'FilesController');
});
