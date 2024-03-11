<?php

namespace Motor\Media\Services;

use Illuminate\Support\Arr;
use Motor\Admin\Models\Category;
use Motor\Admin\Services\BaseService;
use Motor\Core\Filter\Renderers\RelationRenderer;
use Motor\Core\Filter\Renderers\SelectRenderer;
use Motor\Media\Models\File;

/**
 * Class FileService
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

        $this->filter->add(new SelectRenderer('mime_type'))->setOptions(['application/pdf' => 'PDF']);
    }

    public function beforeCreate()
    {
        // check if we have separate description and alt_text fields in the file object
        if (Arr::get($this->data, 'file.description')) {
            $this->data['description'] = Arr::get($this->data, 'file.description');
        }
        if (Arr::get($this->data, 'file.alt_text')) {
            $this->data['alt_text'] = Arr::get($this->data, 'file.alt_text');
        }
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     */
    public function afterCreate()
    {
        $this->upload();
        $this->updateCategories();
        $this->updateTags();
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function afterUpdate()
    {
        $this->upload();
        $this->updateCategories();
        $this->updateTags();
    }

    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    protected function upload()
    {
        $this->uploadFile(Arr::get($this->data, 'file'), 'file');
    }

    protected function updateCategories()
    {
        // Only update categories if they are present in the request
        if (! Arr::get($this->data, 'categories')) {
            return;
        }

        $this->record->categories()
            ->sync(array_filter(Arr::get($this->data, 'categories')));
    }

    protected function updateTags()
    {
        if (Arr::get($this->data, 'tags')) {
            $this->record->syncTags(Arr::get($this->data, 'tags'));
        }
    }
}
