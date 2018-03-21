<?php
Route::group([
    'middleware' => [ 'auth:api', 'bindings', 'permission' ],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'api',
    'as'         => 'api.',
], function () {
    Route::resource('files', 'FilesController');
});

Route::group([
    'middleware' => [ 'web', 'web_auth', 'bindings', 'permission' ],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'ajax',
    'as'         => 'ajax.',
], function () {
    Route::get('files', 'FilesController@index')->name('files.index');
    //Route::resource('files', 'FilesController');
});
