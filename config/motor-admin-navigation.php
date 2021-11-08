<?php

return [
    'items' => [
        850 => [
            'slug'        => 'media',
            'icon'        => 'photo-video',
            'route'       => null,
            'roles'       => ['SuperAdmin'],
            'permissions' => ['media.read'],
            'items'       => [
                100 => [ // <-- !!! replace 239 with your own sort position !!!
                         'slug'        => 'files',
                         'icon'        => 'fa fa-plus',
                         'route'       => 'admin.motor-media.files',
                         'roles'       => ['SuperAdmin'],
                         'permissions' => ['files.read'],
                ],
            ],
        ],
    ],
];
