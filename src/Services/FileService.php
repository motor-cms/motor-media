<?php

namespace Motor\Media\Services;

use Motor\Media\Models\File;
use Motor\Backend\Services\BaseService;

class FileService extends BaseService
{

    protected $model = File::class;

    public function afterCreate()
    {
        $this->upload();
        $this->updateCategories();
    }

    public function afterUpdate()
    {
        $this->upload();
        $this->updateCategories();
    }

    protected function upload()
    {
        $this->uploadFile($this->request->file('file'), 'file');
    }

    protected function updateCategories()
    {
        $this->record->categories()->sync(explode(',', $this->request->get('categories')));
    }
}
