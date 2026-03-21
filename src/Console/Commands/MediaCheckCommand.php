<?php

namespace Motor\Media\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaCheckCommand extends Command
{
    protected $signature = 'motor:media:check
                            {--disk= : Override the disk to check (default: from media-library config)}
                            {--output= : Custom output path for the manifest file}
                            {--headless : Run without interactive output (for cron/CI)}';

    protected $description = 'Check if media files referenced in the database exist on disk and generate a manifest';

    private array $missing = [];

    private int $totalChecked = 0;

    private int $missingOriginals = 0;

    private int $missingConversions = 0;

    private array $conversionNames = ['thumb', 'preview'];

    public function handle(): int
    {
        $diskName = $this->option('disk') ?? config('media-library.disk_name', 'public');
        $isHeadless = $this->option('headless');

        // Fail fast if disk is not accessible
        if (! $this->validateDisk($diskName)) {
            return self::FAILURE;
        }

        $disk = Storage::disk($diskName);
        $totalMedia = Media::count();

        if ($totalMedia === 0) {
            $this->info('No media records found in database.');

            return self::SUCCESS;
        }

        if (! $isHeadless) {
            $this->info("Checking {$totalMedia} media records on disk '{$diskName}'...");
            $this->newLine();
            $progressBar = $this->output->createProgressBar($totalMedia);
            $progressBar->start();
        }

        Media::query()
            ->cursor()
            ->each(function (Media $media) use ($disk, $isHeadless, &$progressBar) {
                $this->checkMediaFile($media, $disk);
                if (! $isHeadless) {
                    $progressBar->advance();
                }
            });

        if (! $isHeadless) {
            $progressBar->finish();
            $this->newLine(2);
        }

        // Write manifest
        $manifestPath = $this->writeManifest();

        // Print summary
        $this->printSummary($manifestPath, $isHeadless);

        return count($this->missing) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function validateDisk(string $diskName): bool
    {
        try {
            $disk = Storage::disk($diskName);
            // Try to list the root directory to verify connectivity
            $disk->directories('/');

            return true;
        } catch (\Exception $e) {
            $this->error("Cannot access disk '{$diskName}': {$e->getMessage()}");

            return false;
        }
    }

    private function checkMediaFile(Media $media, Filesystem $disk): void
    {
        $this->totalChecked++;

        // Check original file
        $originalPath = $media->getPathRelativeToRoot();
        if (! $disk->exists($originalPath)) {
            $this->missingOriginals++;
            $this->missing[] = [
                'media_id' => $media->id,
                'model_type' => $media->model_type,
                'model_id' => $media->model_id,
                'collection' => $media->collection_name,
                'file_name' => $media->file_name,
                'type' => 'original',
                'conversion_name' => null,
                'expected_path' => $originalPath,
                'url' => $media->getFullUrl(),
            ];
        }

        // Check conversions (only for images/videos that would have conversions)
        if ($this->mediaHasConversions($media)) {
            foreach ($this->conversionNames as $conversionName) {
                $conversionPath = $media->getPathRelativeToRoot($conversionName);

                // Skip if conversion path is same as original (conversion doesn't exist)
                if ($conversionPath === $originalPath) {
                    continue;
                }

                if (! $disk->exists($conversionPath)) {
                    $this->missingConversions++;
                    $this->missing[] = [
                        'media_id' => $media->id,
                        'model_type' => $media->model_type,
                        'model_id' => $media->model_id,
                        'collection' => $media->collection_name,
                        'file_name' => $media->file_name,
                        'type' => 'conversion',
                        'conversion_name' => $conversionName,
                        'expected_path' => $conversionPath,
                        'url' => $media->getFullUrl($conversionName),
                    ];
                }
            }
        }
    }

    private function mediaHasConversions(Media $media): bool
    {
        // Check if this media type supports conversions (images and videos)
        $mimeType = $media->mime_type ?? '';

        return str_starts_with($mimeType, 'image/') || str_starts_with($mimeType, 'video/');
    }

    private function writeManifest(): string
    {
        $outputPath = $this->option('output')
            ?? storage_path('logs/media-check-'.Carbon::now()->format('Y-m-d_His').'.json');

        $manifest = [
            'generated_at' => Carbon::now()->toIso8601String(),
            'summary' => [
                'total_checked' => $this->totalChecked,
                'missing_originals' => $this->missingOriginals,
                'missing_conversions' => $this->missingConversions,
                'total_missing' => count($this->missing),
            ],
            'missing' => $this->missing,
        ];

        // Ensure directory exists
        $directory = dirname($outputPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($outputPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $outputPath;
    }

    private function printSummary(string $manifestPath, bool $isHeadless = false): void
    {
        if ($isHeadless) {
            // Simple one-line output for headless mode
            $this->line("checked={$this->totalChecked} missing_originals={$this->missingOriginals} missing_conversions={$this->missingConversions} total_missing=".count($this->missing)." manifest={$manifestPath}");

            return;
        }

        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total media checked', $this->totalChecked],
                ['Missing originals', $this->missingOriginals],
                ['Missing conversions', $this->missingConversions],
                ['Total missing files', count($this->missing)],
            ]
        );

        if (count($this->missing) > 0) {
            $this->newLine();
            $this->warn('Found '.count($this->missing).' missing files.');
        } else {
            $this->newLine();
            $this->info('All files present on disk.');
        }

        $this->newLine();
        $this->info("Manifest written to: {$manifestPath}");
    }
}
