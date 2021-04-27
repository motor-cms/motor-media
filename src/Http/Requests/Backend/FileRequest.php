<?php

namespace Motor\Media\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class FileRequest
 *
 * @package Motor\Media\Http\Requests\Backend
 */
class FileRequest extends Request
{
    /**
     * @OA\Schema(
     *   schema="FileRequest",
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
     *     type="string",
     *     description="Comma separated list of category ids",
     *     example="1,3"
     *   ),
     *   @OA\Property(
     *     property="file",
     *     type="string",
     *     format="binary"
     *   )
     * )
     */

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'nullable',
            'author'      => 'nullable',
            'source'      => 'nullable',
            'alt_text'    => 'nullable',
            'file'        => 'nullable',
            'is_global'   => 'nullable',
            'categories'  => 'required|string',
        ];
    }
}
