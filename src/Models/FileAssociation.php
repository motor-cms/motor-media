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
        'client_id',
        'description',
        'author',
        'source',
        'is_global',
        'alt_text',
    ];


    public function file()
    {
        return $this->belongsTo(File::class);
    }


    public function record()
    {
        return $this->morphTo();
    }
}
