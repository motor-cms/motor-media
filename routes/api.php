<?php

use Motor\Core\Http\Middleware\V2\V2ErrorHandler;
use Motor\Media\Http\Controllers\Api\FilesController;
use Motor\Media\Http\Controllers\Api\V2\FileUsageController;

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('files', FilesController::class);
    });

/*
|--------------------------------------------------------------------------
| V2 Routes (standardized kebab-case naming, consistent response envelope)
|--------------------------------------------------------------------------
*/
Route::prefix('v2')
    ->name('v2.')
    ->middleware(['auth:sanctum', V2ErrorHandler::class])
    ->group(function () {
        Route::apiResource('files', Motor\Media\Http\Controllers\Api\V2\FilesController::class);
        Route::get('files/{file}/usage', [FileUsageController::class, 'show'])
            ->name('files.usage');
    });
