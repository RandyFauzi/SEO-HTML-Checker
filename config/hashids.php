<?php

return [
    'default' => 'main',
    'connections' => [
        'main' => [
            'salt' => env('APP_KEY'),
            'length' => 12,
        ],
    ],
];
