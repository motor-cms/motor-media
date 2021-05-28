<?php
Route::group([
    'middleware' => ['auth:sanctum', 'bindings', 'permission'],
    'namespace'  => 'Motor\Media\Http\Controllers\Api',
    'prefix'     => 'api',
    'as'         => 'api.',
], static function () {
    Route::apiResource('files', 'FilesController');
});
