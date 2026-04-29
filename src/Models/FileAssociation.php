<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Kra8\Snowflake\HasShortflakePrimary;

/**
 * Motor\Media\Models\FileAssociation
 *
 * @property int $id
 * @property int $file_id
 * @property string $model_type
 * @property int $model_id
 * @property string $identifier
 * @property array $custom_properties
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read File $file
 * @property-read Model|\Eloquent $model
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
 *
 * @mixin \Eloquent
 */
class FileAssociation extends Model
{
    use HasShortflakePrimary;

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

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
