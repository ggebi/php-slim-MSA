<?php

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_TOKEN', 902);
define('ERROR_SERVER_UNKNOWN', 999);

return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        'version' => [
            'android' => [
                'ver' => 5,
                'ver_required' => 5,
                'trycount' => -1,
                'refurl' => '',
            ],
            'ios' => [
                'ver' => 3.0,
                'ver_required' => 3.0,
                'trycount' => -1,
                'refurl' => '',
            ],
        ],
        'environment' => getenv('ENVIRONMENT'),
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
