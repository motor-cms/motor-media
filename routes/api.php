<?php

use Motor\Media\Http\Controllers\Api\FilesController;

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
    ->middleware(['auth:sanctum', \Motor\Core\Http\Middleware\V2\V2ErrorHandler::class])
    ->group(function () {
        Route::apiResource('files', \Motor\Media\Http\Controllers\Api\V2\FilesController::class);
    });
