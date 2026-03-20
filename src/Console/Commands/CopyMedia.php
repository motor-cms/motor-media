<?php

namespace Motor\Media\Console\Commands;

use Aws\S3\ObjectUploader;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Motor\Media\Models\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CopyMedia extends Command
{
    protected $signature = 'motor:media:copy-to-s3
                            {--headless : Run without interactive output (for cron/CI)}
                            {--count= : Number of files to upload (default: all)}
                            {--skip=0 : Number of files to skip from the beginning}
                            {--type=sdk : Upload type: "filesystem" or "sdk"}';

    protected $description = 'Copy local media library files to S3 storage';

    private int $copied = 0;

    private int $skipped = 0;

    private int $failed = 0;

    private array $errors = [];

    public function handle(): int
    {
        $isHeadless = $this->option('headless');
        $skip = (int) $this->option('skip');
        $count = $this->option('count') ? (int) $this->option('count') : null;
        $type = $this->option('type');

        // Validate upload type
        if (! in_array($type, ['filesystem', 'sdk'])) {
            $this->error('Invalid upload type. Must be "filesystem" or "sdk".');

            return self::FAILURE;
        }

        // Validate S3 disk is accessible
        if (! $this->validateS3Disk()) {
            return self::FAILURE;
        }

        // Get all files with local media
        $files = File::all();
        $mediaItems = collect();

        foreach ($files as $file) {
            $items = $file->getMedia('file', function (Media $media) {
                return $media->disk === 'media';
            });
            foreach ($items as $item) {
                $mediaItems->push($item);
            }
        }

        $totalAll = $mediaItems->count();

        if ($totalAll === 0) {
            $this->info('No local media files to copy.');

            return self::SUCCESS;
        }

        // Apply skip and count
        if ($skip > 0) {
            $mediaItems = $mediaItems->slice($skip);
        }

        if ($count !== null) {
            $mediaItems = $mediaItems->take($count);
        }

        $total = $mediaItems->count();

        if ($total === 0) {
            $this->info("No media files to copy after skipping {$skip} items.");

            return self::SUCCESS;
        }

        if (! $isHeadless) {
            $skipInfo = $skip > 0 ? " (skipping first {$skip})" : '';
            $this->info("Copying {$total} of {$totalAll} media items to S3 using {$type} method{$skipInfo}...");
            $this->newLine();
            $progressBar = $this->output->createProgressBar($total);
            $progressBar->start();
        }

        $s3 = Storage::disk('media-s3');

        foreach ($mediaItems as $mediaItem) {
            $this->copyMediaItem($mediaItem, $s3, $type);
            if (! $isHeadless) {
                $progressBar->advance();
            }
        }

        if (! $isHeadless) {
            $progressBar->finish();
            $this->newLine(2);
        }

        $this->printSummary($isHeadless);

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function validateS3Disk(): bool
    {
        try {
            Storage::disk('media-s3');

            return true;
        } catch (\Exception $e) {
            $this->error("Cannot access S3 disk: {$e->getMessage()}");

            return false;
        }
    }

    private function copyMediaItem(Media $media, Filesystem $s3, string $type): void
    {
        // Check if local file exists
        if (! file_exists($media->getPath())) {
            $this->errors[] = [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'error' => 'File does not exist locally',
            ];
            $this->failed++;

            return;
        }

        try {
            // Copy original file
            $s3Path = $media->id.'/'.$media->file_name;
            if ($s3->exists($s3Path)) {
                $this->skipped++;
            } else {
                $this->uploadFile($media->getPath(), $s3Path, $s3, $type);
                $this->copied++;
            }

            // Copy conversions
            $conversions = Storage::disk('media')
                ->files($media->id.'/conversions');
            foreach ($conversions as $conversion) {
                if (! $s3->exists($conversion)) {
                    $localPath = public_path().'/media/'.$conversion;
                    if (file_exists($localPath)) {
                        $this->uploadFile($localPath, $conversion, $s3, $type);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'error' => $e->getMessage(),
            ];
            $this->failed++;
            Log::error("Error copying media {$media->id} to S3: {$e->getMessage()}");
        }
    }

    private function uploadFile(string $localPath, string $s3Path, Filesystem $s3, string $type): void
    {
        if ($type === 'filesystem') {
            $s3->put($s3Path, file_get_contents($localPath));
        } else {
            $this->uploadFileUsingSdk($localPath, $s3Path);
        }
    }

    private function uploadFileUsingSdk(string $localPath, string $s3Path): void
    {
        $fileContent = file_get_contents($localPath);

        $s3Client = new S3Client([
            'region' => config('filesystems.disks.media-s3.region'),
            'version' => '2006-03-01',
        ]);

        $bucket = config('filesystems.disks.media-s3.bucket');

        $uploader = new ObjectUploader(
            $s3Client,
            $bucket,
            $s3Path,
            $fileContent
        );

        $uploader->upload();
    }

    private function printSummary(bool $isHeadless): void
    {
        if ($isHeadless) {
            $this->line("copied={$this->copied} skipped={$this->skipped} failed={$this->failed}");

            foreach ($this->errors as $error) {
                $this->error("error: media_id={$error['media_id']} message={$error['error']}");
            }

            return;
        }

        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Copied', $this->copied],
                ['Skipped (already exists)', $this->skipped],
                ['Failed', $this->failed],
            ]
        );

        if (! empty($this->errors)) {
            $this->newLine();
            $this->error('Errors:');
            foreach ($this->errors as $error) {
                $this->line("  - Media #{$error['media_id']} ({$error['file_name']}): {$error['error']}");
            }
        }

        $this->newLine();
        if ($this->failed === 0) {
            $this->info('Copy completed successfully.');
        } else {
            $this->warn('Copy completed with errors.');
        }
    }
}
