<?php

namespace Motor\Media\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Motor\Media\Models\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DeleteLocalMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor-media:delete_local_media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete local media files';

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
                $name = $mediaItem->name;
                try {
                    $mediaItem->forceDelete();
                    $this->info("Deleted {$name}");
                } catch (Exception $e) {
                    $this->error("Error deleting {$name}");
                    $this->error($e->getMessage());
                }
            }
        });
        $this->info('Deletion completed');
    }
}
