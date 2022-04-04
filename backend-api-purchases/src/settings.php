<?php

//회원서비스 에러코드
define('ERROR_UNAUTHORIZED', 201);
define('ERROR_MEMBER_NOT_FOUND', 203);
define('ERROR_SET_POINT', 204);

//컨텐츠 에러코드
define('ERROR_NO_CONTENTS', 301); //컨텐츠 정보 없음

//북마크 에러코드
define('ERROR_NO_BOOKMARKS', 401);

//구매목록 에러코드
define('ERROR_NO_PURCHASES', 501);        //구매목록 정보 없음
define('ERROR_NO_POINT_J', 502);          //제휴컨텐츠 포인트 부족
define('ERROR_NO_POINT_C', 503);          //일반컨텐츠 포인트 부족
define('ERORR_IS_PURCHASE', 504);         //구매목록에 존재
define('ERROR_NO_ACCOUNT', 505);          //충전내역이 없음
define('ERROR_NO_DELETE_PURCHASES', 506); //구매목록 삭제 실패
define('ERROR_DOWN_EXCEEDED', 507);       //다운로드횟수 만료

// 필터링 에러코드
define('ERROR_ONLY_KOREA_AVAILABLE', 602);  //해외차단
define('ERROR_PREVENT_MOBILE_DOWNLOAD', 603);   //모바일다운로드 금지
define('ERROR_BLACKLIST_USER', 604);        //블랙리스트
define('ERROR_FILTERING_FILE_CUT', 605);    //필터링요청 차단컨텐츠

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_SERVER_UNKNOWN', 999);

return [
  'settings' => [
    'determineRouteBeforeAppMiddleware' => true,
    'acl' => [
      'resources' => [
        '/v1/purchases',
        '/v1/purchases/{id}',
        '/v1/purchases/paymentlog',
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
                '/v1/purchases',
                '/v1/purchases/{id}',
                '/v1/purchases/paymentlog',
              ],
              'system' => [
                '/v1/purchases',
                '/v1/purchases/{id}',
                '/v1/purchases/paymentlog',
              ],
              'user' => [
                '/v1/purchases',
                '/v1/purchases/{id}',
                '/v1/purchases/paymentlog',
              ],
          ],
          'deny' => [],
      ],
  ],
    'environment' => getenv('ENVIRONMENT'),
    'salePolicy' => [
      'jehyu_reward_per' => 1,
    ],
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
    'logger' => [
      'scheme' => getenv('LOGS_ENV_SCHEME'),
      'host' => getenv('LOGS_ENV_HOST'),
      'port' => getenv('LOGS_ENV_PORT'),
      'type' => getenv('LOGS_ENV_TYPE'),
      'key' => getenv('LOGS_ENV_REDIS_KEY'),
      'channel' => getenv('LOGS_ENV_CHANNEL'),
    ],
    'site' => [
      'copyidPurchaseKindArr' => [
        'isbs',
        'ikbsm',
        'pregm',
        'candleani',
        'tccompany',
        'imbc',
        'cjenm',
        'jaye',
        'jayecu',
        'jenter',
        'cjenmtv',
        'jayecha',
        'jayetvcs',
        'jayemedialog',
        'jayetco',
        'sbsmdn',
      ],
      // 0. 모바일 필터링 타는 제휴사 - 구 $_MFT['COPYID']
      'useCopyid' => [
        "ikbsm",
        "imbc",
        "cjenmtv",
        "jaye",
        "jayetvcs",
        "jayecu",
        "jayecha",
        "jenter",
        "jayemedialog",
        "jayembn",
        "jayetco",
        "finemovie1",
        "kthanimax",
        "iebs",
        "apexani1",
        "smilecontent",
        "wareg",
        "jayeiconix",
        "jayeshowbox",
      ],
      // 1. PC/모바일 구분 제휴사 (내가 받은자료 PC, 모바일 별도 과금 처리)
      'isPcMobileKindCopyid' => [
        "cjenm",
        "jaye",
        "jayecu",
        "jenter",
        "jayecha",
        "jayetvcs",
        "jayemedialog",
        "jayetco",
        "jayembn",
        "ikbsm",
        "cjenmtv",
        "pregm",
        "imbc",
        "isbs",
        "sbsmdn",
        "tccompany",
        "jayeiconix",
        "jayeshowbox",
      ],
      // 구분 4. 모바일 다운로드/스트리밍 조건 ( 다운로드 최대 3회, 다운로드+스트리밍 10회 )에 해당하는 제휴사
      'mobileLimitDnStCopyid' => [  
        'ikbsm',
      ],
      // 구분 4-1. 모바일 다운로드/스트리밍 조건 ( 다운로드 3회, 스트리밍 무제한 ) 제휴사
      'mobileDownLimitStLimitlessCopyid' => [
        'imbc',
      ],
      // 구분 5. 모바일 스트리밍 유효기간네 무제한인 제휴사
      'mobileStLimitlessCopyid' => [
        'isbs',
        'sbsmdn',
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
        'jayeiconix',
        'jayeshowbox',
      ],
      // 구분 7. 모바일 내가받은자료 유효기간 24시간인 제휴사
      'expireOneDayCopyid' => [
        'ikbsm',
        'pregm',
        'imbc',
        'isbs',
        'sbsmdn',
      ],
      // 구분 7-1. 모바일 내가받은자료 유효기간 48시간인 제휴사
      'expireTwoDayCopyid' => [
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
        'jayeiconix',
        'jayeshowbox',
      ],
      'down_block_copyid' => [
      ],
    ],
  ],
];