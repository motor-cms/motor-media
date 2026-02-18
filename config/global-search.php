<?php

return [
    'modules' => [
        'file' => [
            'index' => 'motor_media_files_index',
            'model' => \Motor\Media\Models\File::class,
            'title_field' => 'file_name',
            'excerpt_field' => 'description',
            'meta_fields' => ['mime_type', 'alt_text', 'thumbnail_url'],
            'default_filter' => null,
        ],
    ],
];
