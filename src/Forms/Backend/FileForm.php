<?php

namespace Motor\Media\Forms\Backend;

use Kris\LaravelFormBuilder\Form;
use Motor\Media\Models\File;

/**
 * Class FileForm
 */
class FileForm extends Form
{
    /**
     * @return mixed|void
     */
    public function buildForm()
    {
        $clients = config('motor-backend.models.client')::pluck('name', 'id')
                                                        ->toArray();
        $this->add('categories', 'hidden')
             ->add('client_id', 'select', [
                 'label'         => trans('motor-backend::backend/clients.client'),
                 'choices'       => $clients,
                 'empty_value'   => trans('motor-backend::backend/global.please_choose'),
                 'rules'         => 'required',
                 'default_value' => config('motor-backend.models.client')::first()->id,
             ])
             ->add('author', 'text', ['label' => trans('motor-media::backend/files.author')])
             ->add('source', 'text', ['label' => trans('motor-media::backend/files.source')])
             ->add('alt_text', 'text', ['label' => trans('motor-media::backend/files.alt_text')])
             ->add('description', 'textarea', ['label' => trans('motor-media::backend/files.description')])
             ->add('is_global', 'checkbox', [
                 'label'         => trans('motor-media::backend/files.is_global'),
                 'default_value' => 1,
             ])
             ->add('file', 'file_file', [
                 'label' => trans('motor-backend::backend/global.file'),
                 'model' => File::class,
             ])
             ->add('submit', 'submit', [
                 'attr'  => ['class' => 'btn btn-primary'],
                 'label' => trans('motor-media::backend/files.save'),
             ]);
    }
}
