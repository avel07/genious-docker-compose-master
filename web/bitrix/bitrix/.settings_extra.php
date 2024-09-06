<?php
return [
    'routing' =>  [
        'value' => [
            'config' => [
                'web.php',
                'api.php',
            ],
        ],
    ],
    'composer' => [
        'value' => [
            'config_path' => 'local/composer.json'
        ]
    ],
    'cache' => [
        'value' => [
            'type' => 'memcache',
            'memcache' => [
                'host' => 'memcached',
                'port' => '11211',
            ],
            'sid' => "prod"
        ]
    ]
];
