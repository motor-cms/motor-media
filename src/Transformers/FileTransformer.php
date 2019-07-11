<?php

namespace Motor\Media\Transformers;

use League\Fractal;
use Motor\Backend\Helpers\MediaHelper;
use Motor\Backend\Transformers\CategoryTransformer;
use Motor\Media\Models\File;

/**
 * Class FileTransformer
 * @package Motor\Media\Transformers
 */
class FileTransformer extends Fractal\TransformerAbstract
{

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [ 'categories' ];

    protected $defaultIncludes = [ 'categories' ];


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
            'id'          => (int) $record->id,
            'description' => $record->description,
            'author'      => $record->author,
            'file'        => MediaHelper::getFileInformation($record, 'file', false, [ 'preview', 'thumb' ]),
        ];
    }


    /**
     * @param File $record
     * @return Fractal\Resource\Collection
     */
    function includeCategories(File $record)
    {
        return $this->collection($record->categories, new CategoryTransformer());
    }
}
