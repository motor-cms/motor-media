<?php

namespace Motor\Media\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Storage;
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
        // check if download is on disk s3 or local
        if ($download->disk == 'media') {
            return response()->download($download->getPath());
        } else {
            // download from s3 instead of just redirecting to file
            // return response()->redirectTo($download->getUrl());
            return redirect(Storage::disk('media-s3')->temporaryUrl(
                $download->getPath(),
                now()->addMinutes(60),
                ['ResponseContentDisposition' => 'attachment']
            ));
        }
    }
}
