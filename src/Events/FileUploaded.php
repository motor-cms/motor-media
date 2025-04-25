<?php

namespace Motor\Media\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Motor\Media\Models\File;

class FileUploaded
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct(File $file)
    {
        Log::info('File uploaded event fired for file '.$file->id);
        Artisan::call('motor-builder:file-update', ['file_id' => $file->id, '--dry-run' => 'false']);
        Log::info('File uploaded event finished for file '.$file->id);
    }
}
