<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'slimframework',
            'username' => 'root',
            'password' => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],
        'security_token' => 'MqeYWhulUMbrCEFiaHSz4I93wNF9wA3H9yZhG45ru66xP0DJY7gQFBlE9SYLi1Q5ezBVLpxqgXVZwHvAXk43O6I9mIZgNaJYyDhb',
    ],
];
