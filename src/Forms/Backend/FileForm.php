<?php

namespace Motor\Media\Forms\Backend;

use Kris\LaravelFormBuilder\Form;
use Motor\Media\Models\File;

class FileForm extends Form
{
    public function buildForm()
    {
        $clients = config('motor-backend.models.client')::lists('name', 'id')->toArray();
        $this
            ->add('categories', 'hidden')
            ->add('client_id', 'select', ['label' => trans('motor-backend::backend/clients.client'), 'choices' => $clients, 'empty_value' => trans('motor-backend::backend/global.please_choose')])
            ->add('author', 'text', ['label' => trans('motor-media::backend/files.author')])
            ->add('source', 'text', ['label' => trans('motor-media::backend/files.source')])
            ->add('alt_text', 'text', ['label' => trans('motor-media::backend/files.alt_text')])
            ->add('description', 'textarea', ['label' => trans('motor-media::backend/files.description')])
            ->add('is_global', 'checkbox', ['label' => trans('motor-media::backend/files.is_global')])
            ->add('file', 'file_file', ['label' =>  trans('motor-backend::backend/global.file'), 'model' => File::class])
            ->add('submit', 'submit', ['attr' => ['class' => 'btn btn-primary'], 'label' => trans('motor-media::backend/files.save')]);
    }
}
