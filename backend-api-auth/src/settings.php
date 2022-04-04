<?php

//인증 에러코드
define("ERROR_LOGIN_FAILED", 101);
define('ERROR_EXPIRED_TOKEN', 102);
define('ERROR_CREATED_TOKEN', 103);
define('ERROR_VALIDATE_TOKEN', 104);

//회원 에러코드
define('ERROR_MEMBER_NOT_FOUND', 203);

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_TOKEN', 902);
define('ERROR_SERVER_UNKNOWN', 999);

//토큰 만료 시간
if (getenv('ENVIRONMENT') == 'yesf-develop') {
    define('EXPIRED_ACCESS_TOKEN_TIME', 60*60*24*7);
}
else {
    define('EXPIRED_ACCESS_TOKEN_TIME', 60*20);
}
define('EXPIRED_REFRESH_TOKEN_TIME', 60*60*24*7);

return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
        // Refresh token settings
        'redis' => [
            'scheme' => getenv('REDIS_ENV_SCHEME'),
            'host' => getenv('REDIS_ENV_HOST'),
            'port' => getenv('REDIS_ENV_PORT'),
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
    ],
];
