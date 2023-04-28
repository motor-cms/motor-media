<?php

namespace Motor\Media\Http\Resources;

use Motor\Admin\Http\Resources\BaseCollection;

class FileCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
