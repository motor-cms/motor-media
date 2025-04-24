<?php

namespace Motor\Media\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class MediaSyncToS3sCommand
 */
class MediaSyncToS3sCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motor:media:sync-to-s3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync media folder';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // First we sync our own files to the s3 bucket
        $this->copyFiles('media', 'do-s3', '/', 'to');

        // Then we loop through all the remote machines that we want to sync to our local folder
        $this->copyFiles('do-s3', 'media', '/', 'from');
    }

    protected function copyFiles($from, $to, $directory, $direction)
    {
        foreach (Storage::disk($from)->files($directory) as $file) {
            // if (Storage::disk($to)->exists($file) && Storage::disk($to)->size($file) != Storage::disk($from)->size($file)) {
            //    $this->info('Deleted local file '.$file);
            //    Storage::disk($to)->delete($file);
            // }
            if (! Storage::disk($to)->exists($file)) {
                Storage::disk($to)->writeStream($file, Storage::disk($from)->readStream($file));

                $log = $direction == 'to' ? 'Sent' : 'Received';

                Log::info($log.' file '.$file);
                $this->info($log.' file '.$file);
            }
        }
        foreach (Storage::disk($from)->directories($directory) as $dir) {
            $this->copyFiles($from, $to, $dir, $direction);
        }
    }
}
