<?php

//컨텐츠 에러코드
define('ERROR_NO_CONTENTS', 301); //컨텐츠 정보 없음

//북마크 에러코드
define('ERROR_NO_BOOKMARKS', 401);
define('ERROR_NO_DELETE_BOOKMARKS', 402);
define('ERROR_DUPLICATED', 403);

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_TOKEN', 902);
define('ERROR_SERVER_UNKNOWN', 999);

return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'acl' => [
            'resources' => [
                '/v1/bookmarks',
                '/v1/bookmarks/{id}',
            ],
            'roles' => [
                'admin',
                'system',
                'user',
                'guest',
            ],
            'assignments' => [
                'allow' => [
                    'admin' => [
                        '/v1/bookmarks',
                        '/v1/bookmarks/{id}',
                    ],
                    'system' => [
                        '/v1/bookmarks',
                        '/v1/bookmarks/{id}',
                    ],
                    'user' => [
                        '/v1/bookmarks',
                        '/v1/bookmarks/{id}',
                    ],
                ],
                'deny' => [],
            ],
        ],
        'environment' => getenv('ENVIRONMENT'),
        'db' => [
            'coredb' => [
                'host' => getenv('DB_ENV_COREDB_HOST'),
                'dbname' => getenv('DB_ENV_COREDB_DATABASE'),
                'user' => getenv('DB_ENV_COREDB_USER'),
                'pass' => getenv('DB_ENV_COREDB_PASSWORD'),
            ],
            'mdb' => [
                'host' => getenv('DB_ENV_MDB_HOST'),
                'dbname' => getenv('DB_ENV_MDB_DATABASE'),
                'user' => getenv('DB_ENV_MDB_USER'),
                'pass' => getenv('DB_ENV_MDB_PASSWORD'),
            ],
            'sdb' => [
                'dbname' => getenv('DB_ENV_SDB_DATABASE'),
                'user' => getenv('DB_ENV_SDB_USER'),
                'pass' => getenv('DB_ENV_SDB_PASSWORD'),
                'fallback' => [
                    "192.168.99.62",
                    "192.168.99.63",
                    "192.168.99.65",
                    "192.168.99.66",
                    "192.168.99.71",
                    "192.168.99.72",
                    "192.168.99.73",
                    "192.168.99.74",
                    "192.168.99.140",
                ],
            ],
        ],
        // Monolog settings
        'logger' => [
            'scheme' => getenv('LOGS_ENV_SCHEME'),
            'host' => getenv('LOGS_ENV_HOST'),
            'port' => getenv('LOGS_ENV_PORT'),
            'type' => getenv('LOGS_ENV_TYPE'),
            'key' => getenv('LOGS_ENV_REDIS_KEY'),
            'channel' => getenv('LOGS_ENV_CHANNEL'),
        ],
    ]
];
