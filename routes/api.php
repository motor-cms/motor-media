<?php

use Motor\Media\Http\Controllers\Api\FilesController;

Route::middleware('auth:sanctum')
    ->group(function () {
        Route::apiResource('files', FilesController::class);
    });
