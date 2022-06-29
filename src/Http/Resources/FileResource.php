<?php

namespace Motor\Media\Http\Resources;

use Exception;
use Motor\Backend\Http\Resources\BaseResource;
use Motor\Backend\Http\Resources\CategoryResource;
use Motor\Backend\Http\Resources\ClientResource;
use Motor\Backend\Http\Resources\MediaResource;

/**
 * @OA\Schema(
 *   schema="FileResource",
 *   @OA\Property(
 *     property="id",
 *     type="integer",
 *     example="1"
 *   ),
 *   @OA\Property(
 *     property="client",
 *     type="object",
 *     ref="#/components/schemas/ClientResource"
 *   ),
 *   @OA\Property(
 *     property="description",
 *     type="string",
 *     example="Exhaustive description of the file"
 *   ),
 *   @OA\Property(
 *     property="author",
 *     type="string",
 *     example="Reza Esmaili"
 *   ),
 *   @OA\Property(
 *     property="source",
 *     type="string",
 *     example="Some photographer"
 *   ),
 *   @OA\Property(
 *     property="alt_text",
 *     type="string",
 *     example="Alternative Text For The IMG Tag"
 *   ),
 *   @OA\Property(
 *     property="is_global",
 *     type="boolean",
 *     example="true"
 *   ),
 *   @OA\Property(
 *     property="file",
 *     type="object",
 *     ref="#/components/schemas/MediaResource"
 *   ),
 *   @OA\Property(
 *     property="categories",
 *     type="array",
 *     @OA\Items(
 *       ref="#/components/schemas/CategoryResource"
 *     ),
 *   ),
 * )
 */
class FileResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // FIXME: why is is like this? do we call the fileresource wrong?
        try {
            $file = new MediaResource($this->getFirstMedia('file'));

            $firstMedia = $this->getFirstMedia('file');
            if (! is_null($firstMedia)) {
                $exists = file_exists($firstMedia->getPath());
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
                    $exists = file_exists($firstMedia->getPath());
                }
                $categories = CategoryResource::collection($this->file->categories);
            } catch (Exception $e) {
                // do nothing
            }
        }

        return [
            'id'          => (int) $this->id,
            'client'      => new ClientResource($this->client),
            'description' => $this->description,
            'author'      => $this->author,
            'source'      => $this->source,
            'is_global'   => $this->is_gobal,
            'alt_text'    => $this->alt_text,
            'file'        => (isset($file) ? $file : null),
            'categories'  => (isset($categories) ? $categories : null),
            'exists'      => isset($exists) ? $exists : false,
        ];
    }
}
