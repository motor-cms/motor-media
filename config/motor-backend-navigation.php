<?php

return [
    'items' => [
        850 => [
            'slug'        => 'media',
            'name'        => 'motor-media::backend/global.media',
            'icon'        => 'fa fa-home',
            'route'       => null,
            'roles'       => ['SuperAdmin'],
            'permissions' => ['media.read'],
            'items'       => [
                100 => [ // <-- !!! replace 239 with your own sort position !!!
                    'slug'        => 'files',
                    'name'        => 'motor-media::backend/files.files',
                    'icon'        => 'fa fa-plus',
                    'route'       => 'backend.files.index',
                    'roles'       => ['SuperAdmin'],
                    'permissions' => ['files.read'],
                ],
            ],
        ],
    ],
];
