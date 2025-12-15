<?php

namespace Motor\Media\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseCollection;

class FileCollection extends BaseCollection
{
    public $collects = FileResource::class;

    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
