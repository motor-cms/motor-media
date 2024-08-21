<?php

namespace Motor\Media\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Motor\Media\Models\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CopyMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor-media:copy_media_to_s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        File::all()->each(function ($model) {
            $mediaItems = $model->getMedia('file', function(Media $media) {
                return $media->disk == 'media';
            });
            $modelname = $model->description;
            foreach ($mediaItems as $mediaItem) {
                if ($mediaItem->name == 'other.png') {
                    $this->info("other");
                }
                try {
                    $copiedItem = $mediaItem->copy($model, 'file', 'media-s3');
                    $name = $copiedItem->name;
                    $this->info("Copied ${name}");
                } catch (Exception $e) {}
            }
        });
        $this->info("Disk copy completed.");
    }
}
