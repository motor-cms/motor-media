<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\Collection;
use Kra8\Snowflake\HasShortflakePrimary;
use Laravel\Scout\Searchable;
use Mattiverse\Userstamps\Traits\Userstamps;
use Motor\Admin\Models\Category;
use Motor\Core\Traits\BelongsToClient;
use Motor\Core\Traits\Filterable;
use Motor\Media\Database\Factories\FileFactory;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

/**
 * Motor\Media\Models\File
 *
 * @property int $id
 * @property int|null $client_id
 * @property string $description
 * @property string $author
 * @property string $source
 * @property string $alt_text
 * @property bool $is_global
 * @property bool $is_excluded_from_search_index
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection|FileAssociation[] $fileAssociations
 * @property-read int|null $file_associations_count
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 *
 * @method static Builder|File filteredBy(\Motor\Core\Filter\Filter $filter, $column)
 * @method static Builder|File filteredByMultiple(\Motor\Core\Filter\Filter $filter)
 * @method static Builder|File newModelQuery()
 * @method static Builder|File newQuery()
 * @method static Builder|File query()
 * @method static Builder|File search($query, $full_text = false)
 * @method static Builder|File whereAltText($value)
 * @method static Builder|File whereAuthor($value)
 * @method static Builder|File whereClientId($value)
 * @method static Builder|File whereCreatedAt($value)
 * @method static Builder|File whereCreatedBy($value)
 * @method static Builder|File whereDeletedBy($value)
 * @method static Builder|File whereDescription($value)
 * @method static Builder|File whereId($value)
 * @method static Builder|File whereIsGlobal($value)
 * @method static Builder|File whereSource($value)
 * @method static Builder|File whereUpdatedAt($value)
 * @method static Builder|File whereUpdatedBy($value)
 *
 * @mixin \Eloquent
 */
class File extends Model implements HasMedia
{
    use BelongsToClient;
    use Filterable;
    use HasFactory;
    use HasShortflakePrimary;
    use HasTags;
    use InteractsWithMedia;
    use LogsActivity;
    use Searchable;
    use Userstamps;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => $eventName);
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'motor_media_files_index';
    }

    public function toSearchableArray()
    {
        $media = $this->getFirstMedia('file');
        $file_name = $media ? $media->file_name : '';
        $mime_type = $media ? $media->mime_type : '';

        // Public storage/CDN URL of the original file — same resolution as
        // MediaResource. The admin search UI shares this URL via "copy link",
        // so it must not point at the (VPN-only) backend /download route.
        $url = '';
        if ($media) {
            $urlPrefix = Storage::disk('media')->url($media->id);
            $prependAppUrl = true;
            if (config('filesystems.has_s3')) {
                $s3 = Storage::disk('media-s3');
                if ($s3->exists('media/'.$media->id.'/'.$media->file_name)) {
                    $urlPrefix = $s3->url('media/'.$media->id);
                    $prependAppUrl = false;
                }
            }
            $url = ($prependAppUrl ? config('app.url') : '').$urlPrefix.'/'.$media->file_name;
        }

        return [
            'description'                   => $this->description,
            'author'                        => $this->author,
            'alt_text'                      => $this->alt_text,
            'source'                        => $this->source,
            'client_id'                     => $this->client_id ? (int) $this->client_id : null,
            // Index the File record's own timestamps so the list/gallery can sort
            // by them (declared sortable in config/scout.php). Stored as Unix
            // timestamps for correct numeric ordering in Meilisearch. The File
            // created_at is stable across media replacements, unlike the Spatie
            // media-row date — ZRMDEV-220.
            'created_at'                    => $this->created_at?->timestamp,
            'updated_at'                    => $this->updated_at?->timestamp,
            'file_name'                     => $file_name,
            'file.file_name'                => $file_name,
            'mime_type'                     => $mime_type,
            'file.mime_type'                => $mime_type,
            'thumbnail_url'                 => $this->getFirstMediaUrl('file', 'thumb') ? config('app.url').$this->getFirstMediaUrl('file', 'thumb') : '',
            'url'                           => $url,
            'categories'                    => $this->categories->pluck('id')
                ->toArray(),
            'tags'                          => $this->tags->pluck('name')
                ->toArray(),
            'is_excluded_from_search_index' => $this->is_excluded_from_search_index,
        ];
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media->mime_type == 'image/gif') {
            $this->addMediaConversion('thumb')
                ->keepOriginalImageFormat()
                ->nonOptimized()
                ->nonQueued();
            $this->addMediaConversion('preview')
                ->keepOriginalImageFormat()
                ->nonOptimized()
                ->nonQueued();
        } else {
            $this->addMediaConversion('thumb')
                ->width(400)
                ->height(400)
                ->keepOriginalImageFormat()
                ->extractVideoFrameAtSecond(10)
                ->nonQueued();
            $this->addMediaConversion('preview')
                ->width(1920)
                ->height(1080)
                ->keepOriginalImageFormat()
                ->extractVideoFrameAtSecond(10)
                ->nonQueued();
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'description',
        'author',
        'source',
        'is_global',
        'alt_text',
        'is_excluded_from_search_index',
    ];

    protected static function newFactory(): FileFactory
    {
        return FileFactory::new();
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function fileAssociations(): HasMany
    {
        return $this->hasMany(FileAssociation::class);
    }
}
