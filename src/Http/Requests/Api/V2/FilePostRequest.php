<?php

namespace Motor\Media\Http\Requests\Api\V2;

use Motor\Core\Http\Requests\ValidatesAgainstUserClients;
use Motor\Media\Http\Requests\Backend\FilePostRequest as V1FilePostRequest;

class FilePostRequest extends V1FilePostRequest
{
    use ValidatesAgainstUserClients;

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['client_id'] = array_merge($rules['client_id'] ?? ['nullable', 'integer'], [
            $this->allowedClientIdsRule(),
        ]);

        return $rules;
    }
}
