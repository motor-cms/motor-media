<?php

return [
    'media' => [
        'name'   => 'motor-media::backend/global.media',
        'values' => [
            'read',
        ],
    ],
    'files' => [
        'name'   => 'motor-media::backend/files.files',
        'values' => [
            'read',
            'write',
            'delete',
        ],
    ],
];
