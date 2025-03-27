<?php

namespace Motor\Media\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Motor\Media\Models\File;

class FileDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct(File $file)
    {
        Log::info('File deleted event fired for file '.$file->id);
        Artisan::call('motor-builder:file-delete', ['file_id' => $file->id, '--dry-run' => 'false']);
        Log::info('File deleted event finished for file '.$file->id);
    }
}
