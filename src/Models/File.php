<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Kra8\Snowflake\HasShortFlakePrimary;
use Illuminate\Database\Eloquent\Model;
use Motor\Backend\Models\Category;
use Motor\Core\Filter\Filter;
use Motor\Core\Traits\Filterable;
use Motor\Core\Traits\Searchable;
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
 * @property-read \Kalnoy\Nestedset\Collection|\Motor\Backend\Models\Category[] $categories
 * @property-read \Motor\Backend\Models\User $creator
 * @property-read \Motor\Backend\Models\User|null $eraser
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Models\Media[] $media
 * @property-read \Motor\Backend\Models\User $updater
 *
 * @method static Builder|File filteredBy(Filter $filter, $column)
 * @method static Builder|File filteredByMultiple(Filter $filter)
 * @method static Builder|File newModelQuery()
 * @method static Builder|File newQuery()
 * @method static Builder|File query()
 * @method static Builder|File search($q, $full_text = false)
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
 * @mixin \Eloquent
 */
class File extends Model implements HasMedia
{
    use Searchable;
    use Filterable;
    use BlameableTrait;
    use InteractsWithMedia;
    use HasShortFlakePrimary;

    /**
     * @param  Media|null  $media
     *
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
     * Searchable columns for the searchable trait
     *
     * @var array
     */
    protected $searchableColumns = ['description', 'author', 'source', 'alt_text'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
