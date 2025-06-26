<?php

namespace Motor\Media\Http\Requests\Backend;

use Motor\Admin\Http\Requests\Request;

class FilePostRequest extends Request
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
            'client_id' => [
                'nullable',
                'integer',
            ],
            'description' => [
                'nullable',
            ],
            'author' => [
                'nullable',
            ],
            'source' => [
                'nullable',
            ],
            'alt_text' => [
                'nullable',
            ],
            'is_global' => [
                'nullable',
            ],
            'categories' => [
                'required',
                'array',
                'min:1',
            ],
            'categories.*' => [
                'exists:categories,id',
            ],
            'files' => [
                'required',
                'array',
                'min:1',
            ],
            'file' => [
                'nullable',
            ],
            'files.*.dataUrl' => [
                'required',
                'string',
            ],
            'files.*.name' => [
                'nullable',
                'string',
            ],
        ];
    }
}
