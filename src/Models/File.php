<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Motor\Backend\Models\Category;
use Motor\Core\Traits\Searchable;
use Motor\Core\Traits\Filterable;
use Culpa\Traits\Blameable;
use Culpa\Traits\CreatedBy;
use Culpa\Traits\DeletedBy;
use Culpa\Traits\UpdatedBy;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * Motor\Media\Models\File
 *
 * @property int                                                                               $id
 * @property int|null                                                                          $client_id
 * @property string                                                                            $description
 * @property string                                                                            $author
 * @property string                                                                            $source
 * @property string                                                                            $alt_text
 * @property int                                                                               $is_global
 * @property int                                                                               $created_by
 * @property int                                                                               $updated_by
 * @property int|null                                                                          $deleted_by
 * @property \Illuminate\Support\Carbon|null                                                   $created_at
 * @property \Illuminate\Support\Carbon|null                                                   $updated_at
 * @property-read \Kalnoy\Nestedset\Collection|\Motor\Backend\Models\Category[]                $categories
 * @property-read \Motor\Backend\Models\User                                                   $creator
 * @property-read \Motor\Backend\Models\User|null                                              $eraser
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Models\Media[] $media
 * @property-read \Motor\Backend\Models\User                                                   $updater
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File filteredBy( \Motor\Core\Filter\Filter $filter, $column )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File filteredByMultiple(\Motor\Core\Filter\Filter $filter )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File search( $q, $full_text = false )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereAltText( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereAuthor( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereClientId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereCreatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereCreatedBy( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereDeletedBy( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereDescription( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereId( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereIsGlobal( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereSource( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereUpdatedAt( $value )
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\File whereUpdatedBy( $value )
 * @mixin \Eloquent
 */
class File extends Model implements HasMedia
{

    use Searchable;
    use Filterable;
    use Blameable, CreatedBy, UpdatedBy, DeletedBy;
    use HasMediaTrait;


    /**
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
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
     *
     * @var array
     */
    protected $blameable = [ 'created', 'updated', 'deleted' ];

    /**
     * Searchable columns for the searchable trait
     *
     * @var array
     */
    protected $searchableColumns = [ 'description', 'author', 'source', 'alt_text' ];

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
