<?php

namespace Motor\Media\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Motor\Media\Models\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MigrateMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'motor-media:migrate_media_to_s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate (move) media files to S3';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        File::all()->each(function ($model) {
            $mediaItems = $model->getMedia('file', function(Media $media) {
                return $media->disk == 'media';
            });
            foreach ($mediaItems as $mediaItem) {
                try {
                    $movedItem = $mediaItem->move($model, 'file', 'media-s3');
                    $name = $movedItem->name;
                    $this->info("Migrated {$name}");
                } catch (Exception $e) {
                    $this->error("Error migrating {$mediaItem->name}");
                    $this->error($e->getMessage());
                }
            }
        });
        $this->info("Disk migration completed.");
    }
}
