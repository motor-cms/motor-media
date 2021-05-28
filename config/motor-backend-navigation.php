<?php

return [
    'items' => [
        850 => [
            'slug'        => 'media',
            'name'        => 'motor-media.global.media',
            'icon'        => 'photo-video',
            'route'       => null,
            'roles'       => ['SuperAdmin'],
            'permissions' => ['media.read'],
            'items'       => [
                100 => [ // <-- !!! replace 239 with your own sort position !!!
                         'slug'        => 'files',
                         'name'        => 'motor-media.files.files',
                         'icon'        => 'fa fa-plus',
                         'route'       => 'admin.motor-media.files',
                         'roles'       => ['SuperAdmin'],
                         'permissions' => ['files.read'],
                ],
            ],
        ],
    ],
];
