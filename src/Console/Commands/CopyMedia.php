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
    protected $description = 'Copy media files to S3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        File::all()->each(function ($model) {
            $mediaItems = $model->getMedia('file', function (Media $media) {
                return $media->disk == 'media';
            });
            foreach ($mediaItems as $mediaItem) {
                $this->info("Copying {$mediaItem->name}");
                try {
                    $copiedItem = $mediaItem->copy($model, 'file', 'media-s3');
                    $name = $copiedItem->name;
                    $this->info("Copied {$name}");
                } catch (Exception $e) {
                    $this->error("Error copying {$mediaItem->name}");
                    $this->error($e->getMessage());
                }
            }
        });
        $this->info('Disk copy completed.');
    }
}
