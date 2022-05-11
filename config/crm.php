<?php

return [

    'avatarFiles' => [
        'maxBytes' => 2000000,
        'types' => ['jpg', 'jpeg', 'gif', 'png'],
    ],
    /**
     *  Directories and dimensions for Avatar files.
     *  The 'dir' value is relative to the /storage/app folder,
     *  and should have a trailing and leading slash.
     **/
    'avatars' => [
        'tmp' => [
            'dir' => '/public/avatars/tmp/',
        ],
        'placeholder' => [
            'dir' => '/public/avatars/placeholder/',
            'width' => 8,
            'height' => 8,
        ],
        'small' => [
            'dir' => '/public/avatars/small/',
            'width' => 40,
            'height' => 40,
        ],
        'medium' => [
            'dir' => '/public/avatars/medium/',
            'width' => 100,
            'height' => 100,
        ],
        'large' => [
            'dir' => '/public/avatars/large/',
            'width' => 500,
            'height' => 500,
        ],
    ],
];
