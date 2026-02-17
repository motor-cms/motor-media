<?php

namespace Motor\Media\Http\Resources\V2;

use Exception;
use Motor\Admin\Http\Resources\CategoryResource;
use Motor\Admin\Http\Resources\ClientResource;
use Motor\Admin\Http\Resources\MediaResource;
use Motor\Core\Http\Resources\V2\BaseResource;

class FileResource extends BaseResource
{
    public function toArray($request): array
    {
        try {
            $file = new MediaResource($this->getFirstMedia('file'));
            $firstMedia = $this->getFirstMedia('file');
            if (! is_null($firstMedia)) {
                $exists = file_exists($firstMedia->getPath()) || $firstMedia->disk == 'media-s3';
            }
            $categories = CategoryResource::collection($this->categories);
        } catch (Exception $e) {
            // do nothing
        }

        if (! is_null($this->file)) {
            try {
                $file = new MediaResource($this->file->getFirstMedia('file'));
                $firstMedia = $this->file->getFirstMedia('file');
                if (! is_null($firstMedia)) {
                    $exists = file_exists($firstMedia->getPath()) || $firstMedia->disk == 'media-s3';
                }
                $categories = CategoryResource::collection($this->file->categories);
            } catch (Exception $e) {
                // do nothing
            }
        }

        return [
            'id' => (int) $this->id,
            'client_id' => $this->client_id,
            'client' => new ClientResource($this->whenLoaded('client', $this->client)),
            'description' => $this->description,
            'author' => $this->author,
            'source' => $this->source,
            'is_global' => $this->is_global,
            'alt_text' => $this->alt_text,
            'file' => $file ?? null,
            'categories' => $categories ?? null,
            'exists' => $exists ?? false,
            'is_excluded_from_search_index' => (bool) $this->is_excluded_from_search_index,
            'tags' => $this->tags->pluck('name'),
        ];
    }
}
