<?php

namespace Motor\Media\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Motor\Media\Helpers\S3Helper;
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
        File::all()
            ->each(function ($model) {
                $mediaItems = $model->getMedia('file', function (Media $media) {
                    return $media->disk == 'media';
                });
                foreach ($mediaItems as $mediaItem) {
                    if (!file_exists($mediaItem->getPath())) {
                        $this->error("File does not exist locally {$mediaItem->name}");
                    } else {
                        S3Helper::uploadToS3($mediaItem);
                    }
                }
            });
        $this->info('Disk copy completed.');
    }

    //protected function checkS3($media, $filename)
    //{
    //    $s3 = \Storage::disk('media-s3');
    //    if ($s3->exists($media->id.'/'.$filename)) {
    //        $this->info("File exists on s3 {$media->id} {$filename}");
    //    } else {
    //        $s3->put($media->id.'/'.$filename, file_get_contents($media->getPath()), 'public');
    //        $this->info("Copying to s3 {$media->id} {$filename}");
    //    }
    //    // Check conversions and upload them
    //    foreach (Storage::disk('media')->files($media->id . '/conversions') as $conversion) {
    //        if ($s3->exists($conversion)) {
    //            $this->info("Conversion exists on s3 {$conversion}");
    //        } else {
    //            $s3->put($conversion, file_get_contents(public_path().'/media/'.$conversion), 'public');
    //            $this->info("Copying conversion to s3 {$conversion}");
    //        }
    //    }
    //}
}
