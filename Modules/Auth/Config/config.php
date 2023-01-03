<?php

return [
    'name' => 'Auth',

    'sanctum_token' => env('SANCTUM_TOKEN', 'https://laravel.com'),

    'roles' => [
        'admin',
        'customer'
    ]
];
