<?php

use Motor\Media\Http\Controllers\Frontend\DownloadsController;

Route::get('download/{file}', [DownloadsController::class, 'index']);
