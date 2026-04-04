<?php

namespace Motor\Media\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaSyncToS3sCommand extends Command
{
    protected $signature = 'motor:media:sync-to-s3
                            {--direction=both : Sync direction (to, from, both)}
                            {--dry-run : Show what would be synced without copying}';

    protected $description = 'Sync media folder to/from S3';

    public function handle()
    {
        $direction = $this->option('direction');

        if (!in_array($direction, ['to', 'from', 'both'])) {
            $this->error("Invalid direction '{$direction}'. Use: to, from, both");
            return 1;
        }

        if ($direction === 'to' || $direction === 'both') {
            $this->syncDisk('media', 'do-s3', 'Sent');
        }

        if ($direction === 'from' || $direction === 'both') {
            $this->syncDisk('do-s3', 'media', 'Received');
        }
    }

    protected function syncDisk(string $from, string $to, string $label): void
    {
        $this->info("Listing files on {$from}...");
        $sourceFiles = $this->allFiles($from);
        $this->info("  Found " . count($sourceFiles) . " files on {$from}");

        $this->info("Listing files on {$to}...");
        $targetFiles = array_flip($this->allFiles($to));
        $this->info("  Found " . count($targetFiles) . " files on {$to}");

        $missing = array_filter($sourceFiles, fn ($file) => !isset($targetFiles[$file]));
        $this->info("  " . count($missing) . " files to sync");

        if (count($missing) === 0) {
            return;
        }

        $bar = $this->output->createProgressBar(count($missing));
        $bar->start();

        $failed = 0;

        foreach ($missing as $file) {
            if ($this->option('dry-run')) {
                $this->line(" [dry-run] {$label} {$file}");
                $bar->advance();
                continue;
            }

            try {
                $stream = Storage::disk($from)->readStream($file);
                if ($stream === null) {
                    Log::warning("Skipped {$file}: could not open read stream from {$from}");
                    $this->warn(" Skipped: {$file}");
                    $failed++;
                    $bar->advance();
                    continue;
                }
                Storage::disk($to)->writeStream($file, $stream);
                Log::info("{$label} file {$file}");
            } catch (\Throwable $e) {
                Log::error("Failed to sync {$file}: {$e->getMessage()}");
                $this->warn(" Failed: {$file}");
                $failed++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $synced = count($missing) - $failed;
        $this->info("{$label} {$synced} files" . ($failed ? ", {$failed} failed" : ''));
    }

    protected function allFiles(string $disk): array
    {
        return Storage::disk($disk)->allFiles('/');
    }
}
