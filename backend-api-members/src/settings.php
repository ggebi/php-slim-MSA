<?php

//회원 에러코드
define('ERROR_UNAUTHORIZED', 201);			//권한 없음
define('ERROR_VERIFY_PASSWORD', 202);		//회원 검증 실패
define('ERROR_MEMBER_NOT_FOUND', 203);	//회원 정보 없음
define('ERROR_SET_POINT', 204);				//포인트 수정 실패
define('ERROR_SET_CONFIG', 205);			//설정 변경 실패

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_SERVER_UNKNOWN', 999);

return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'acl' => [
            'resources' => [
                "/v1/members",
                "/v1/members/{userid}",
                "/v1/members/{userid}/point",
                "/v1/members/{userid}/configs",
                "/v1/members/verify",
                "/v1/members/{userName}/blacklist",
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
                        "/v1/members",
                        "/v1/members/{userid}",
                        "/v1/members/{userid}/point",
                        "/v1/members/{userid}/configs",
                        "/v1/members/verify",
                        "/v1/members/{userName}/blacklist",
                    ],
                    'system' => [
                        "/v1/members",
                        "/v1/members/{userid}",
                        "/v1/members/{userid}/point",
                        "/v1/members/{userid}/configs",
                        "/v1/members/verify",
                        "/v1/members/{userName}/blacklist",
                    ],
                    'user' => [
                        "/v1/members/{userid}",
                        "/v1/members/{userid}/point",
                        "/v1/members/{userid}/configs",
                        "/v1/members/{userName}/blacklist",
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
