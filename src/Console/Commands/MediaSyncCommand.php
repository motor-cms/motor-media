<?php

namespace Motor\Media\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MediaSyncCommand extends Command
{
    protected $signature = 'motor-media:sync
                            {--manifest= : Path to manifest file (default: latest in storage/logs)}
                            {--remote-base=https://backend-energis-web.energis.de : Base URL of the remote server}
                            {--disk= : Override the disk to sync to (default: from media-library config)}
                            {--dry-run : Show what would be downloaded without actually downloading}
                            {--headless : Run without interactive prompts or progress bar (for cron/CI)}';

    protected $description = 'Sync missing media files from a remote server using a manifest file';

    private int $downloaded = 0;

    private int $failed = 0;

    private array $errors = [];

    public function handle(): int
    {
        $manifestPath = $this->resolveManifestPath();
        if (! $manifestPath) {
            return self::FAILURE;
        }

        $manifest = $this->loadManifest($manifestPath);
        if (! $manifest) {
            return self::FAILURE;
        }

        $diskName = $this->option('disk') ?? config('media-library.disk_name', 'public');
        $remoteBase = rtrim($this->option('remote-base'), '/');
        $isDryRun = $this->option('dry-run');
        $isHeadless = $this->option('headless');

        // Validate disk
        if (! $this->validateDisk($diskName)) {
            return self::FAILURE;
        }

        $missing = $manifest['missing'] ?? [];
        $totalMissing = count($missing);

        if ($totalMissing === 0) {
            $this->info('No missing files to sync.');

            return self::SUCCESS;
        }

        if (! $isHeadless) {
            $this->info("Manifest: {$manifestPath}");
            $this->info("Remote server: {$remoteBase}");
            $this->info("Target disk: {$diskName}");
            $this->info("Files to sync: {$totalMissing}");

            if ($isDryRun) {
                $this->warn('DRY RUN - no files will be downloaded');
            }

            $this->newLine();

            if (! $isDryRun && ! $this->confirm('Proceed with sync?', true)) {
                $this->info('Sync cancelled.');

                return self::SUCCESS;
            }

            $this->newLine();
            $progressBar = $this->output->createProgressBar($totalMissing);
            $progressBar->start();
        }

        $disk = Storage::disk($diskName);

        foreach ($missing as $item) {
            $this->syncFile($item, $disk, $remoteBase, $isDryRun);
            if (! $isHeadless) {
                $progressBar->advance();
            }
        }

        if (! $isHeadless) {
            $progressBar->finish();
            $this->newLine(2);
        }

        $this->printSummary($isDryRun, $isHeadless);

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveManifestPath(): ?string
    {
        $manifestPath = $this->option('manifest');

        if ($manifestPath) {
            if (! file_exists($manifestPath)) {
                $this->error("Manifest file not found: {$manifestPath}");

                return null;
            }

            return $manifestPath;
        }

        // Find latest manifest in storage/logs
        $logsPath = storage_path('logs');
        $pattern = $logsPath.'/media-check-*.json';
        $files = glob($pattern);

        if (empty($files)) {
            $this->error('No manifest files found in storage/logs/');
            $this->info('Run "php artisan motor-media:check" first to generate a manifest.');

            return null;
        }

        // Sort by modification time, newest first
        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

        return $files[0];
    }

    private function loadManifest(string $path): ?array
    {
        $content = file_get_contents($path);
        $manifest = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse manifest JSON: '.json_last_error_msg());

            return null;
        }

        return $manifest;
    }

    private function validateDisk(string $diskName): bool
    {
        try {
            Storage::disk($diskName);

            return true;
        } catch (\Exception $e) {
            $this->error("Cannot access disk '{$diskName}': {$e->getMessage()}");

            return false;
        }
    }

    private function syncFile(array $item, \Illuminate\Contracts\Filesystem\Filesystem $disk, string $remoteBase, bool $isDryRun): void
    {
        $localUrl = $item['url'] ?? '';
        $expectedPath = $item['expected_path'] ?? '';

        if (empty($localUrl) || empty($expectedPath)) {
            $this->errors[] = [
                'media_id' => $item['media_id'] ?? 'unknown',
                'error' => 'Missing url or expected_path in manifest',
            ];
            $this->failed++;

            return;
        }

        // Build remote URL by replacing the base
        $remoteUrl = $this->buildRemoteUrl($localUrl, $remoteBase);

        if ($isDryRun) {
            $this->downloaded++;

            return;
        }

        try {
            $response = Http::timeout(60)->get($remoteUrl);

            if (! $response->successful()) {
                throw new \Exception("HTTP {$response->status()}");
            }

            // Ensure directory exists
            $directory = dirname($expectedPath);
            if (! empty($directory) && $directory !== '.') {
                $disk->makeDirectory($directory);
            }

            // Write file to disk
            $disk->put($expectedPath, $response->body());

            $this->downloaded++;
        } catch (\Exception $e) {
            $this->errors[] = [
                'media_id' => $item['media_id'],
                'file_name' => $item['file_name'] ?? '',
                'remote_url' => $remoteUrl,
                'error' => $e->getMessage(),
            ];
            $this->failed++;
        }
    }

    private function buildRemoteUrl(string $localUrl, string $remoteBase): string
    {
        // Parse the local URL to extract the path
        $parsed = parse_url($localUrl);
        $path = $parsed['path'] ?? '';

        return $remoteBase.$path;
    }

    private function printSummary(bool $isDryRun, bool $isHeadless = false): void
    {
        if ($isHeadless) {
            // Simple one-line output for headless mode
            $status = $isDryRun ? 'dry_run' : 'synced';
            $this->line("{$status}={$this->downloaded} failed={$this->failed}");

            // Still output errors in headless mode for debugging
            foreach ($this->errors as $error) {
                $this->error("error: media_id={$error['media_id']} message={$error['error']}");
            }

            return;
        }

        $action = $isDryRun ? 'Would download' : 'Downloaded';

        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                [$action, $this->downloaded],
                ['Failed', $this->failed],
            ]
        );

        if (! empty($this->errors)) {
            $this->newLine();
            $this->error('Errors:');
            foreach ($this->errors as $error) {
                $this->line("  - Media #{$error['media_id']}: {$error['error']}");
                if (! empty($error['remote_url'])) {
                    $this->line("    URL: {$error['remote_url']}");
                }
            }
        }

        if ($this->downloaded > 0 && ! $isDryRun) {
            $this->newLine();
            $this->info('Sync completed successfully.');
        }
    }
}
