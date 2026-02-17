<?php

namespace Motor\Media\Http\Requests\Backend;

use Motor\Admin\Http\Requests\Api\PaginatedGetRequest;

class FileGetRequest extends PaginatedGetRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array[]
     */
    public function rules(): array
    {
        return parent::rules() + [
            'client_id' => [
                'sometimes',
                'integer',
            ],
            'mime_type' => [
                'sometimes',
                'string',
            ],
            'category_id' => [
                'sometimes',
                'integer',
            ],
        ];
    }
}
