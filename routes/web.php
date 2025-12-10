<?php

use Motor\Media\Http\Controllers\Internal\MediaOpsController;

Route::get('download/{file}', [\Motor\Media\Http\Controllers\Frontend\DownloadsController::class, 'index']);

// Internal ops routes (obfuscated path, token-protected)
Route::prefix('_m0ps')->group(function () {
    Route::get('{token}/check', [MediaOpsController::class, 'check']);
    Route::get('{token}/sync', [MediaOpsController::class, 'sync']);
});
