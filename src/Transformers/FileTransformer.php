<?php

namespace Motor\Media\Transformers;

use League\Fractal;
use Motor\Media\Models\File;

class FileTransformer extends Fractal\TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];


    /**
     * Transform record to array
     *
     * @param File $record
     *
     * @return array
     */
    public function transform(File $record)
    {
        return [
            'id'        => (int) $record->id
        ];
    }
}
