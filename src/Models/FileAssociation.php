<?php

namespace Motor\Media\Models;

use Illuminate\Database\Eloquent\Model;

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
