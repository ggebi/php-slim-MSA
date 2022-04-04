<?php

//필터링 에러코드
define('ERROR_INVALID_FILTER', 601);
define('ERROR_ONLY_KOREA_AVAILABLE', 602);      //해외차단
define('ERROR_PREVENT_MOBILE_DOWNLOAD', 603);   //모바일다운로드 금지
define('ERROR_BLACKLIST_USER', 604);            //블랙리스트
define('ERROR_FILTERING_FILE_CUT', 605);        //제휴파일 차단
define('ERROR_NO_HASH', 606);                   //해쉬파일 조회 내용 없음
define('ERROR_SOCKER_FAILED', 607);             //소켓오픈 실패

//공통 에러코드
define('ERROR_INVALID_PARAM', 901);
define('ERROR_TOKEN', 902);
define('ERROR_SERVER_UNKNOWN', 999);

return [
  'settings' => [
    'determineRouteBeforeAppMiddleware' => true,
    'acl' => [
      'resources' => [
        '/v1/filters',
        '/v1/filters/paylog',
        '/v1/filters/imbclog',
      ],
      'roles' => [
        'admin',
        'system',
        'user',
        'guest',
      ],
      'assignments' => [
        'allow' => [
          'user' => [
            '/v1/filters',
            '/v1/filters/paylog',
            '/v1/filters/imbclog',
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
    'filtering_site_info' => [
      'OSP_NAME' => 'YESFILE',
      'OSP_KEY' => '1c00679cf22bf70426ddf210766a41b8',
      // 'FT_URL' => 'https://112.175.183.10/sfiltersite/m2_video_h.jsp', // 필터링 요청 URL
      // 'PL_URL' => 'https://112.175.183.10/sfiltersite/m2_video_report.jsp', // Pay Log 전송URL
      'FT_URL' => 'https://sfiltersite.candlemedia.co.kr/sfiltersite/m2_video_h.jsp', // Filtering 요청 URL
      'PL_URL' => 'https://sfiltersite.candlemedia.co.kr/sfiltersite/m2_video_report.jsp', // Pay Log 전송 URL
      'IMBC_URL' => 'webhard.imbc.com', // IMBC Log 전송 URL
      'IMBC_PATH' => '/real/realMobileLog.do', //IMBC Path
    ],
    'site' => [
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
      // 구분 2. PC/모바일 해외 IP 구매 차단 제휴사
      'isNotKoreaCutCopyid' => [
        'cjenm',
        'jaye',
        'jayecu',
        'jenter',
        'jayecha',
        'jayetvcs',
        'jayemedialog',
        'jayetco',
        'jayembn',
        'ikbsm',
        'cjenmtv',
        'imbc',
        'isbs',
        'sbsmdn',
        'channelw',
        'jayeiconix',
        'jayeshowbox',
      ],
      // 구분 6. 모바일 다운로드 불가 제휴사
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
      'pcVipBlock' => [
        'imbc',
      ],
    ],
  ],
];