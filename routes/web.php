<?php

Route::get('download/{file}', [\Motor\Media\Http\Controllers\Frontend\DownloadsController::class, 'index']);
