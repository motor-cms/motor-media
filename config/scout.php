<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://docs.meilisearch.com/guides/advanced_guides/configuration.html
    |
    */

    'meilisearch' => [
        'index-settings' => [
            \Motor\Media\Models\File::class => [
                'filterableAttributes' => ['categories', 'mime_type'],
                'sortableAttributes'   => ['files.name', 'file.file_name', 'file.mime_type', 'files.id', 'author', 'created_at', 'updated_at', 'id'],
                'rankingRules' => ['sort', 'words', 'typo', 'proximity', 'attribute', 'exactness'],
            ],
        ],
    ],
];
