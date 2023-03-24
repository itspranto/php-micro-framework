<?php

return [
    'debug' => false,
    'tpl_dir' => "templates",
    'cache_dir' => "cache",

    // Middlewares
    'middlewares' => [
        //'session' => 'Pranto\Middleware\BasicMiddlewares::startSession',
        'json' => 'Pranto\Middleware\BasicMiddlewares::jsonResponse'
    ],
];