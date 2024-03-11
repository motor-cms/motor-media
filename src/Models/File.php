<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Laravel\Scout\Searchable;
use Motor\Admin\Models\Category;
use Motor\Core\Traits\Filterable;
use Motor\Media\Database\Factories\FileFactory;
use RichanFongdasen\EloquentBlameable\BlameableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
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
 * @property int $is_global
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection|Category[] $categories
 * @property-read int|null $categories_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $media
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
    use Searchable;
    use Filterable;
    use BlameableTrait;
    use InteractsWithMedia;
    use HasFactory;
    use HasShortflakePrimary;
    use HasTags;

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'motor_media_files_index';
    }

    public function toSearchableArray()
    {
        return [
            'description'                   => $this->description,
            'author'                        => $this->author,
            'alt_text'                      => $this->alt_text,
            'source'                        => $this->source,
            'file_name'                     => $this->getFirstMedia('file') ? $this->getFirstMedia('file')->file_name : '',
            'mime_type'                     => $this->getFirstMedia('file') ? $this->getFirstMedia('file')->mime_type : '',
            'categories'                    => $this->categories->pluck('id')
                ->toArray(),
            'tags'                          => $this->tags->pluck('name')
                ->toArray(),
            'is_excluded_from_search_index' => $this->is_excluded_from_search_index,
        ];
    }

    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
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

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
