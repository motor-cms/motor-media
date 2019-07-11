<?php

namespace Motor\Media\Http\Requests\Backend;

use Motor\Backend\Http\Requests\Request;

/**
 * Class FileRequest
 * @package Motor\Media\Http\Requests\Backend
 */
class FileRequest extends Request
{

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

        ];
    }
}
