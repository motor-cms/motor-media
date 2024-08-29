<?php

namespace Motor\Media\Http\Requests\Backend;

use Motor\Admin\Http\Requests\Request;

/**
 * Class FilePatchRequest
 *
 * @OA\Schema(
 *   schema="FilePatchRequest",
 *
 *   @OA\Property(
 *     property="client_id",
 *     type="integer",
 *     example="1"
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
 *     property="categories",
 *     type="array",
 *     description="Array of category ids",
 *
 *     @OA\Items(
 *       anyOf={
 *
 *         @OA\Schema(type="integer")
 *       }
 *     ),
 *     example="[1,3]"
 *   ),
 *
 *   @OA\Property(
 *     property="file",
 *     type="object",
 *     ref="#/components/schemas/FileUpload",
 *     description="If false, the existing file will be deleted"
 *   ),
 * )
 */
class FilePatchRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'client_id'    => [
                'nullable',
                'integer',
            ],
            'description'  => [
                'nullable',
            ],
            'author'       => [
                'nullable',
            ],
            'source'       => [
                'nullable',
            ],
            'alt_text'     => [
                'nullable',
            ],
            'is_global'    => [
                'nullable',
            ],
            'categories'   => [
                'required',
                'array',
                'min:1',
            ],
            'file'         => [
                'nullable',
            ],
            'file.dataUrl' => [
                'nullable',
                'string',
            ],
            'file.name'    => [
                'nullable',
                'string',
            ],
        ];
    }
}
