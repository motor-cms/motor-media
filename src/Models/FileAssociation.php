<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Motor\Media\Models\FileAssociation
 *
 * @property int $id
 * @property int $file_id
 * @property string $model_type
 * @property int $model_id
 * @property string $identifier
 * @property string $custom_properties
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Motor\Media\Models\File $file
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $model
 *
 * @method static Builder|FileAssociation newModelQuery()
 * @method static Builder|FileAssociation newQuery()
 * @method static Builder|FileAssociation query()
 * @method static Builder|FileAssociation whereCreatedAt($value)
 * @method static Builder|FileAssociation whereCustomProperties($value)
 * @method static Builder|FileAssociation whereFileId($value)
 * @method static Builder|FileAssociation whereId($value)
 * @method static Builder|FileAssociation whereIdentifier($value)
 * @method static Builder|FileAssociation whereModelId($value)
 * @method static Builder|FileAssociation whereModelType($value)
 * @method static Builder|FileAssociation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FileAssociation extends Model
{

    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_id',
        'model_id',
        'model_type',
        'identifier',
        'custom_properties',
        'alt_text',
    ];

    protected $casts = [
        'custom_properties' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }
}
