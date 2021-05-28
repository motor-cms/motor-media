<?php

namespace Motor\Media\Services;

use Illuminate\Support\Arr;
use Motor\Backend\Models\Category;
use Motor\Backend\Services\BaseService;
use Motor\Core\Filter\Renderers\RelationRenderer;
use Motor\Media\Models\File;

/**
 * Class FileService
 *
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
                              ->get();

        $options = [];
        foreach ($categories as $key => $category) {
            $returnValue = '';
            $ancestors = (int) $category->ancestors()
                                        ->count();
            while ($ancestors > 1) {
                $returnValue .= '&nbsp;&nbsp;&nbsp;&nbsp;';
                $ancestors--;
            }

            $options[$category->id] = $returnValue.$category->name;
        }

        $this->filter->add(new RelationRenderer('category_id'))
                     ->setJoin('category_file')
                     ->setEmptyOption('-- '.trans('motor-backend::backend/categories.categories').' --')
                     ->setOptions($options);
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
        $this->uploadFile(Arr::get($this->data, 'file'), 'file');
    }

    protected function updateCategories()
    {
        $this->record->categories()
                     ->sync(array_filter(Arr::get($this->data, 'categories')));
    }
}
