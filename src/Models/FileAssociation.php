<?php

namespace Motor\Media\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereCustomProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Motor\Media\Models\FileAssociation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FileAssociation extends Model
{

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


    public function file()
    {
        return $this->belongsTo(File::class);
    }


    public function model()
    {
        return $this->morphTo();
    }
}
