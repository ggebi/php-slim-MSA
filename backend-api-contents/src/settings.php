<?php

//컨텐츠 에러코드
define('ERROR_NO_CONTENTS', 301);				//컨텐츠 정보 없음
define('ERROR_NO_MOBILE', 302);					//모바일 컨텐츠 아님
define('ERROR_DOWNLOAD_POLICY', 303);           //다운로드 URL POLICY 실패
define('ERROR_IS_NOT_SERIES', 304);             //회차별 자료가 없는 컨텐츠

//구매목록 에러코드
define('ERROR_NO_PURCHASES', 501);

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_TOKEN', 902);
define('ERROR_SERVER_UNKNOWN', 999);


return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'acl' => [
            'resources' => [
                '/v1/contents',
                '/v1/contents/{id}/files',
                '/v1/contents/downurl',
                '/v1/contents/recommends/movie',
                '/v1/contents/series/{id}',
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
                        '/v1/contents',
                        '/v1/contents/{id}/files',
                        '/v1/contents/downurl',
                        '/v1/contents/recommends/movie',
                        '/v1/contents/series/{id}',
                    ],
                    'system' => [
                        '/v1/contents',
                        '/v1/contents/{id}/files',
                        '/v1/contents/recommends/movie',
                        '/v1/contents/series/{id}',
                    ],
                    'user' => [
                        '/v1/contents',
                        '/v1/contents/{id}/files',
                        '/v1/contents/downurl',
                        '/v1/contents/recommends/movie',
                        '/v1/contents/series/{id}',
                    ],
                    'guest' => [
                        '/v1/contents',
                        '/v1/contents/{id}/files',
                        '/v1/contents/recommends/movie',
                        '/v1/contents/series/{id}',
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
        'urlsign_info' => [
            'requestHost' => 'ultron.bankmedia.co.kr',
            'expireTime' => (60 * 60 * 3),
        ],
        'site' => [
            'mobileDnNotCopyid' => [
                'cjenm',
                'jaye',
                'jayecu',
                'jenter',
                'jayecha',
                'jayetvcs',
                'jayemedialog',
                'jayetco',
                'jayembn',
                'cjenmtv',
                'isbs',
                'sbsmdn',
                'jayeiconix',
                'jayeshowbox',
            ],
            'double_charge_cate' => [
                "BD_DM",
                "BD_UC",
                "BD_AN",
                "BD_CT",
                "BD_DC",
                "BD_IM",
                "BD_AD"
            ],
            'down_block_copyid' => [

            ],
        ],
    ]
];
