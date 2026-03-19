<?php

namespace Motor\Media\Http\Resources\V2;

use Motor\Core\Http\Resources\V2\BaseResource;

class FileUsageResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => (int) $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'is_published' => (bool) $this->is_published,
            'block_types' => $this->block_types,
        ];
    }
}
