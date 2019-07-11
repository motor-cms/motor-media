<?php

namespace Motor\Media\Services;

use Motor\Backend\Models\Category;
use Motor\Core\Filter\Renderers\RelationRenderer;
use Motor\Media\Models\File;
use Motor\Backend\Services\BaseService;

/**
 * Class FileService
 * @package Motor\Media\Services
 */
class FileService extends BaseService
{

    protected $model = File::class;


    public function filters()
    {
        $categories = Category::where('scope', 'media')
                              ->where('_lft', '>', 1)
                              ->orderBy('_lft', 'ASC')
                              ->pluck('name', 'id');
        $this->filter->add(new RelationRenderer('category_id'))
                     ->setJoin('category_file')
                     ->setEmptyOption('-- ' . trans('motor-backend::backend/categories.categories') . ' --')
                     ->setOptions($categories);
    }


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
