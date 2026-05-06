<?php

namespace Motor\Media\Filter\Renderers;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Builder as ScoutBuilder;
use Motor\Core\Filter\Renderers\SelectRenderer;

/**
 * MIME-type filter that accepts either a full MIME (e.g. `image/png`, exact
 * match) or a short bucket keyword (e.g. `image`, expanded to all MIMEs in
 * that bucket).
 *
 * `mime_type` lives on the Spatie media row, not the `files` table, so the
 * filter joins through `whereHas('media', …)` against the canonical `file`
 * media collection. This works for both raw Eloquent queries and Scout-driven
 * queries via `Builder::query()`.
 */
class MimeTypeRenderer extends SelectRenderer
{
    /**
     * Short bucket keyword → full set of MIME types covered by it.
     *
     * @var array<string, list<string>>
     */
    public const BUCKETS = [
        'image' => [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/avif',
            'image/bmp',
            'image/tiff',
            'image/heic',
            'image/heif',
        ],
        'video' => [
            'video/mp4',
            'video/quicktime',
            'video/webm',
            'video/x-msvideo',
            'video/x-matroska',
            'video/mpeg',
            'video/ogg',
        ],
        'audio' => [
            'audio/mpeg',
            'audio/mp4',
            'audio/wav',
            'audio/ogg',
            'audio/webm',
            'audio/x-m4a',
            'audio/flac',
        ],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'text/plain',
            'text/csv',
            'application/rtf',
        ],
    ];

    public function query(Builder|ScoutBuilder $query): object
    {
        $value = $this->getValue();
        $values = self::BUCKETS[$value] ?? [$value];

        $apply = fn (Builder $q) => $q->whereHas(
            'media',
            fn ($m) => $m->where('collection_name', 'file')
                ->whereIn('mime_type', $values)
        );

        if ($query instanceof ScoutBuilder) {
            // Defer to Scout's post-search rehydrate step so the join runs
            // against the IDs the engine returned, not the whole table.
            $query->query($apply);

            return $query;
        }

        return $apply($query);
    }
}
