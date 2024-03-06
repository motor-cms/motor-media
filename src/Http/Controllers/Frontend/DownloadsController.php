<?php

namespace Motor\Media\Http\Controllers\Frontend;

use Motor\Admin\Http\Controllers\ApiController;
use Motor\Media\Models\File;

/**
 * Class FilesController
 */
class DownloadsController extends ApiController
{
    public function index(int $fileId)
    {
        $file = File::find($fileId);
        if (! $file) {
            abort(404);
        }
        $download = $file->getFirstMedia('file');
        if (! $download) {
            abort(404);
        }

        if (! file_exists($download->getPath())) {
            abort(404);
        }

        return response()->download($file->getFirstMedia('file')->getPath());
    }
}
