<?php

namespace Motor\Media\Helpers;

use Illuminate\Support\Facades\Storage;
use Log;

class S3Helper
{
    public static function uploadToS3($media)
    {
        $s3 = \Storage::disk('media-s3');
        if ($s3->exists($media->id.'/'.$media->file_name)) {
            Log::info("File exists on s3 {$media->id} {$media->file_name}");
        } else {
            $s3->put($media->id.'/'.$media->file_name, file_get_contents($media->getPath()), 'public');
            Log::info("Copying to s3 {$media->id} {$media->file_name}");
        }
        // Check conversions and upload them
        foreach (Storage::disk('media')
                        ->files($media->id.'/conversions') as $conversion) {
            if ($s3->exists($conversion)) {
                Log::info("Conversion exists on s3 {$conversion}");
            } else {
                $s3->put($conversion, file_get_contents(public_path().'/media/'.$conversion), 'public');
                Log::info("Copying conversion to s3 {$conversion}");
            }
        }
    }

    public static function uploadCustomConversion($media, $directory, $filename)
    {
        try {
            $s3 = \Storage::disk('media-s3');
            if ($s3->exists($media->id.'/conversions/'.$filename)) {
                $s3->delete($media->id.'/conversions/'.$filename);
            }
            $s3->put($media->id.'/conversions/'.$filename, file_get_contents($directory.$filename), 'public');
            \Illuminate\Support\Facades\Log::info('Copying conversion to s3', [$directory.$filename]);
        } catch (\Exception $e) {
            Log::error('Error in copying conversion to s3: '.$e->getMessage(), [$directory.$filename]);
        }
    }

    public static function getConversion($media, $filename)
    {
        try {
            $s3 = \Storage::disk('media-s3');
            if ($s3->exists($media->id.'/conversions/'.$filename)) {
                return $s3->url($media->id.'/conversions/'.$filename);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getting conversion from s3: '.$e->getMessage(), [$media->id.'/conversions/'.$filename]);
        }

        return url('/media/'.$media->id.'/conversions/'.$filename);
    }
}
