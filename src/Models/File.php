<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasShortflakePrimary;
use Motor\Admin\Models\Category;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
use Motor\Media\Database\Factories\FileFactory;
use RichanFongdasen\EloquentBlameable\BlameableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    /**
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->format('png')
            ->extractVideoFrameAtSecond(10)
            ->nonQueued();
        $this->addMediaConversion('preview')
            ->width(400)
            ->height(400)
            ->format('png')
            ->extractVideoFrameAtSecond(10)
            ->nonQueued();
    }

    /**
     * Columns for the Blameable trait
     */
  //  protected array $blameable = ['created', 'updated', 'deleted'];

    /**
     * Searchable columns for the searchable trait
     */
    protected array $searchableColumns = ['description', 'author', 'source', 'alt_text'];

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
