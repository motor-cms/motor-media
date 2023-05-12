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
                'filterableAttributes' => [],
                'sortableAttributes'   => ['files.id', 'author', 'created_at', 'updated_at', 'id'],
            ],
        ],
    ],
];
