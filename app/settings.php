<?php
return [
    'settings' => [
        'prefixApi' => '/api/v1',
        'tc24' => [
            'url' => 'http://tc24-api-beta.test',
            'prefixApi' => '/ygt/v1',
            'buildUrl' => 'http://tc24-api-beta.test/ygt/v1',
        ],
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Monolog settings
        'logger' => [
            'name' => 'app',
            'path' => __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Doctrine settings
        'doctrine' => [
            'meta' => [
                'entity_path' => [
                    __DIR__ . '/../app/src/Entity'
                ],
                'auto_generate_proxies' => true,
                'proxy_dir' =>  __DIR__ . '/../cache/proxies',
                'cache' => null,
            ],
            'connection' => [
                'driver'   => 'pdo_mysql',
                'host'     => 'localhost',
                'dbname'   => 'tc24-external-api',
                'user'     => 'root',
                'password' => '',
            ]
        ]

    ],
];
