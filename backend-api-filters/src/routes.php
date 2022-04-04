<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Bankmedia\Models\Filters;
use Bankmedia\Common\Profile;
use GeoIp2\Database\Reader;

/**
 * @api GET /filters            컨텐츠 필터링
 * @api POST /filters/paylog    제휴컨텐츠 페이로그
 * @api POST /filters/imbclog   IMBC 제휴컨텐츠 페이로그
 */
$app->group('/v1', function() {
  $copyidValidator = v::alpha();
  $contentValidator = v::numeric();
  $filterValidator = v::alpha(',')->noWhitespace();
  $validators = array(
    'filters' => array(
      'copyid' => $copyidValidator,
    ),
  );
  
  /**
   * 제휴컨텐츠 필터링
   * @param string  copyid
   * @param string  title
   * @param string  file_list
   * @return status code
   * @todo validation 적용해야함
   */
  $this->get('/filters', function(Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    if ($request->getAttribute('has_errors')) {
      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');
      $userid = $token['userid'];
      $filtering_site_info = $this->get('settings')['filtering_site_info'];
      $filtering_copyid = $this->get('settings')['site'];
      $copyid = $request->getParsedBodyParam('copyid');
      $bbs_idx = $request->getParsedBodyParam('bbs_idx');
      $title = $request->getParsedBodyParam('title');
      $file_list = $request->getParsedBodyParam('file_list');

      $file_idx_list = '';
      foreach ($file_list as $v) {
        $file_idx_list .= $v['file_idx'].',';
      }
      $file_idx_list = rtrim($file_idx_list, ',');
    
      // 1. 해외 ip 차단 제휴사 
      if (in_array($copyid, $filtering_copyid['isNotKoreaCutCopyid'])) {
        $country = '';
        try {
          $reader = new Reader('/var/www/GeoLite2-Country.mmdb');
          // $record = $reader->country('121.140.159.182');
          $record = $reader->country($log_form['ipAddress']);
          $country = $record->country->isoCode;
        }
        catch (\Exception $e) {
          $this->logger->error(
            sprintf('geoip error ip[%s] msg[%s]', $log_form['ipAddress'], $e->getMessage()),
            array_merge($log_form, ['keyword' => 'FILTER_IP', 'duration' => $profile->end('ACTION')])
          );
        }

        $this->logger->debug(
          sprintf('country[%s]', $country),
          array_merge($log_form, ['keyword' => 'FILTER_IP'])
        );

        if (strlen($country) && ($country !== 'KR')) {
          $this->logger->info(
            sprintf('필터링 해외 IP 차단 컨텐츠:%d', $bbs_idx),
            array_merge($log_form, ['keyword' => 'FILTER_IP', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_ONLY_KOREA_AVAILABLE, 'errorMessage' => '해당 컨텐츠는 방송사의 요청에 의해 다운로드가 허용되지 않습니다.'], 200);
        }
      }

      // 2. 모바일 다운로드 제한 제휴사
      if (in_array($copyid, $filtering_copyid['mobileDnNotCopyid'])) {
        $this->logger->info(
          sprintf('필터링 모바일 다운로드 제한 컨텐츠:%d', $bbs_idx),
          array_merge($log_form, ['keyword' => 'FILTER_IP', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_PREVENT_MOBILE_DOWNLOAD, 'errorMessage' => '해당 컨텐츠는 방송사의 요청에 의해 다운로드가 허용되지 않습니다.'], 200);
      }

      // 3. 컨텐츠 차단 확인 (_copy_contents)
      $ssomon_hash = '';
      try {
        $profile->start('FILTER_SSOMON');

        $filters = new Filters($this->sdb);
        $ssomon_hash = $filters->getSsomonHash($file_idx_list);
        if (count($ssomon_hash) < 1) {
          $this->logger->warning(
            sprintf('쏘몬해쉬 정보 없음 컨텐츠:%d', $bbs_idx),
            array_merge($log_form, ['keyword' => 'FILTER_SSOMON', 'duration' => $profile->end('ACTION')])
          );

          // return $response->withJson(['errorCode' => ERROR_NO_HASH, 'errorMessage' => '쏘몬해쉬 정보 없음'], 404);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('쏘몬해쉬 정보 조회 실패 컨텐츠:%d 메세지:%s', $bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'FILTER_SSOMON', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
      }

      $this->logger->info(
        sprintf('쏘몬해쉬 정보 조회 완료 컨텐츠:%d', $bbs_idx),
        array_merge($log_form, ['keyword' => 'FILTER_SSOMON', 'duration' => $profile->end('FILTER_SSOMON')])
      );

      /**
       * ssomon_hash의 content_id 정보로 _copy_contents에서 차단상태를 확인 state=3이면 차단된 컨텐츠
       * @todo ssomon_hash 테이블에 컬럼은 있지만, value가 채워지지 않고있음 확인사항!!
       */
      $contentid_list = '';
      foreach ($ssomon_hash as $v) {
        $contentid_list .= $v['video_right_content_id'].',';
      }
      $contentid_list = rtrim($contentid_list, ',');

      try {
        $profile->start('FILTER_COPY_CONTENTS');

        $ret = $filters->chkContentIdFile($contentid_list);
        if ((true === is_array($ret)) && (count($ret) > 0)) {
          $this->logger->info(
            sprintf('제휴 컨텐츠 파일 차단 컨텐츠:%d', $bbs_idx),
            array_merge($log_form, ['keyword' => 'FILTER_COPY_CONTENTS', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_FILTERING_FILE_CUT, 'errorMessage' => '해당 컨텐츠는 방송사의 요청에 의해 다운로드가 허용되지 않습니다.'], 200);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('제휴 컨텐츠 파일 차단 조회 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'FILTER_COPY_CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
      }

      $this->logger->info(
        sprintf('제휴컨텐츠 파일차단 조회 완료:%d', $bbs_idx),
        array_merge($log_form, ['keyword' => 'FILTER_COPY_CONTENTS', 'duration' => $profile->end('FILTER_COPY_CONTENTS')])
      );

      if (in_array($copyid, $filtering_copyid['useCopyid'])) {
        // 4. 필터링사에 컨텐츠 파일 차단여부 확인
        $key_value = $filtering_site_info['OSP_NAME'].$filtering_site_info['OSP_KEY'].date("YmdHis", time());
        $key_md5 = md5($key_value);

        for ($i=0; $i<count($file_list); $i++) {
          for ($j=0; $j<count($ssomon_hash); $j++) {
            if ($file_list[$i]['fileidx'] == $ssomon_hash[$j]['fileidx']) {
              $file_list[$i]['mureka_hash'] = $ssomon_hash[$j]['mureka_hash'];
              break;
            }
          }
        }

        $request_response_arr = array();
        $request_data['site']       = $filtering_site_info['OSP_NAME'];
        $request_data['key']        = $key_md5;
        $request_data['format']     = 'mp4';
        $request_data['userid']     = $userid;
        $request_data['type']       = 'DN'; //다운로드 DN : 스트리밍 ST
        $request_data['bbs_idx']    = $bbs_idx;
        $request_data['bbs_title']  = $title;

        $fk = 0;
        $i = 0;

        if (getenv('ENVIRONMENT') !== 'yesfile-develop') {
          $filtering_url = $filtering_site_info['FT_URL'];
        }

        foreach ($file_list as $v) {
          $profile->start('FILTERING');

          $request_data['uhash']    = $v['mureka_hash'];
          $request_data['mhash']    = $v['mureka_hash'];
          $request_data['filename'] = $v['realname'];
          $request_data['filesize'] = $v['mmsv_size'];

          // 필터링 요청하기 전 리퀘스트 로깅
          $this->logger->info(
            '필터링사 필터링 요청',
            array_merge(
              $log_form,
              [
                'keyword' => 'FILTERING',
                'filteringRequest' => $request_data,
              ]
            )
          );
          
          $request_data_arr = http_build_query($request_data);
          $request_http_arr['method'] = 'POST';
          $request_http_arr['header'] = 'Content-type: application/x-www-form-urlencoded';
          $request_http_arr['content'] = $request_data_arr;

          $opts['http'] = $request_http_arr;
          $context  = stream_context_create($opts);
          $response_data = @file_get_contents($filtering_url, false, $context);
          $response_data_iconv = iconv("EUC-KR", "UTF-8", $response_data);
          $response_data_arr = json_decode($response_data_iconv, true);

          // 펄터링 요청 이후 리스폰스 로깅
          $this->logger->info(
            '필터링사 필터링 응답',
            array_merge(
              $log_form,
              [
                'keyword' => 'FILTERING',
                'duration' => $profile->end('FILTERING'),
                'filteringResponseIconv' => $response_data_iconv,
              ]
            )
          );

          // paylog 전송시 필요
          if ($response_data_arr[1]['STATUS'] == "0") {
            $filtering_response_arr[$fk]['filtering_key']  = $response_data_arr[2]['INFO']['filtering_key'];
            $filtering_response_arr[$fk]['content_id']     = $response_data_arr[2]['INFO']['content_id'];
            $filtering_response_arr[$fk]['ch_content_id']  = $response_data_arr[2]['INFO']['ch_content_id'];
            $filtering_response_arr[$fk]['file_idx']        = $v['file_idx'];
            $fk++;
          }
          
          if ($response_data_arr[2]['INFO']['rmi_code'] == "10"){ # 차단 컨텐츠
            $this->logger->info(
              sprintf('필터링 차단:%d', $bbs_idx),
              array_merge($log_form, ['keyword' => 'FILTERING', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_FILTERING_FILE_CUT, 'errorMessage' => '해당 컨텐츠는 방송사의 요청에 의해 다운로드가 허용되지 않습니다.'], 200);
          }
        }

        $this->logger->info(
          '필터링 성공',
          array_merge(
            $log_form,
            [
              'keyword' => 'ACTION',
              'duration' => $profile->end('ACTION'),
              'filteringResponseArr' => $filtering_response_arr,
            ]
          )
        );

        /**
         * $respons_data_jd : [
         *     {
         *         "RESPONSE": "hash_video/right_video",
         *     },
         *     {
         *         "STATUS" : "0"
         *     },
         *     {
         *         "INFO" : {
         *             "rmi_code" : "10",
         *             "content_id" : "메타 컨텐츠 ID",
         *             "title" : "컨텐츠 제목",
         *             "seq" : "컨텐츠 회차",
         *             "price" : "가격",
         *             "ch_id" : "권리사 아이디",
         *             "ch_name" : "권리사 명",
         *             "ch_content_id" : "권리사 컨텐츠 아이디",
         *             "release_date" : "방영일",
         *             filtering_key" : "필터링키",
         *         },
         *         
         *     }, 
         * ]
         * 
         */

        return $response->withJson($filtering_response_arr, 200);
      } // useCopyid

      $this->logger->info(
        '필터링 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withStatus(200);
    }
  });

  $this->post('/filters/paylog', function(Request $request, Response $response, array $args) {
    if ($request->getAttribute('has_errors')) {
      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $profile = new Profile();
      $profile->start('ACTION');

      $token = $request->getAttribute('token');
      $log_form = $request->getAttribute('session')['log_form'];
      $tid = $log_form['transactionID'];
      $tdepth = $log_form['transactionDepth'];
      $filtering_site_info = $this->get('settings')['filtering_site_info'];

      $NOW_TIME = $request->getParsedBodyParam('now_time');
      $MODE = $request->getParsedBodyParam('mode');
      $sell_userid = $request->getParsedBodyParam('sell_userid');
      $bbs_idx = $request->getParsedBodyParam('bbs_idx');
      $content_title = $request->getParsedBodyParam('content_title');
      $filter_files = $request->getParsedBodyParam('filter_files');

      if (getenv('ENVIRONMENT') !== 'yesfile-develop') {
        $paylog_url = $filtering_site_info['PL_URL'];
      }

      $filters = new Filters($this->sdb);

      // @todo worker 구축 전까지는 직접 paylog 전송
      $req_info = [
        'site' => $filtering_site_info['OSP_NAME'],
        'key' => $filters->mobile_filterauthkey_maker($filtering_site_info['OSP_NAME'], $filtering_site_info['OSP_KEY'], $NOW_TIME),
        'paid_datetime' => date("YmdHis", $NOW_TIME),
        'paid_userid' => $token['userid'],
        'sell_userid' => $sell_userid,
        'bbs_idx' => $bbs_idx,
        'bbs_title' => iconv('UTF-8', 'EUC-KR', $content_title),
      ];

      try {
        foreach ($filter_files as $v) {
          $profile->start('PAYLOG');

          $this->logger->info(
            '필터링사 페이로그 요청',
            array_merge(
              $log_form,
              [
                'keyword' => 'PAYLOG',
                'paylogRequest' => array_merge($v, $req_info),
              ]
            )
          );

          $request_data_arr = http_build_query(
            array_merge(
              $v,
              $req_info
            )
          );
          $request_http_arr['method'] = 'POST';
          $request_http_arr['header'] = 'Content-type: application/x-www-form-urlencoded';
          $request_http_arr['content'] = $request_data_arr;

          $opts['http'] = $request_http_arr;
          $context      = @stream_context_create($opts);
          $response_data = @file_get_contents($paylog_url, false, $context);

          $this->logger->info(
            '필터링사 페이로그 응답',
            array_merge(
              $log_form,
              [
                'keyword' => 'PAYLOG',
                'duration' => $profile->end('PAYLOG'),
                'paylogResponse' => $response_data,
              ]
            )
          );
        }
        /**
         * [{
         *  "RESPONSE": "log_pay",
         *  "STATUS": "0"
         * }]
         * 
         * STATUS: 성공여부(0: 성공, 1: 실패)
         */

         // @todo 에러 상황 처리 할 것
      }
      catch (\Exception $e) {
        $this->logger->warning(
          sprintf('필터링사 페이로그 실패 메세지:%s', $e->getMessage),
          array_merge(
            $log_form,
            [
              'keyword' => 'PAYLOG',
              'duration' => $profile->end('ACTION'),
            ]
          )
        );
      }

      $this->logger->info(
        sprintf('페이로그 성공:%d', $bbs_idx),
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withStatus(200);
    }
  });

  $this->post('/filters/imbclog', function(Request $request, Response $response, array $args) {
    if ($request->getAttribute('has_errors')) {
      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $profile = new Profile();
      $profile->start('ACTION');

      $token = $request->getAttribute('token');
      $log_form = $request->getAttribute('session')['log_form'];
      $tid = $log_form['transactionID'];
      $tdepth = $log_form['transactionDepth'];
      $filtering_site_info = $this->get('settings')['filtering_site_info'];
      $send_data_arr = $request->getParsedBodyParam('send_data_arr');
      $body_data = array(
        'mdate' => date('YmdHis'),
        'ospid' => 'yesfile',
        'orderid' => '',
        'sellerid' => $request->getParsedBodyParam('sellerid'),
        'buyerid' => $request->getParsedBodyParam('buyerid'),
        'payid' => $request->getParsedBodyParam('payid'),
        'webck' => $request->getParsedBodyParam('webck'),
        'bbs_title' => $request->getParsedBodyParam('bbs_title'),
        'bbs_num' => $request->getParsedBodyParam('bbs_num'),
      );
      
      // 로컬 테스트에서 로그 보내지 않도록 조건 설정
      if (getenv('ENVIRONMENT') !== 'yesfile-develop') {
        $remote_ip = $filtering_site_info['IMBC_URL'];
        $remote_path = $filtering_site_info['IMBC_PATH'];
      }

      foreach ($send_data_arr as $data_arr) {
        $profile->start('IMBC_LOG');

        $data_arr = array_merge($data_arr, $body_data);

        foreach ($data_arr as $k => $v) {
          $send_data[] = $k . '=' . $v;
        }

        $this->logger->info(
          'IMBC 로깅 요청',
          array_merge(
            $log_form,
            [
              'keyword' => 'IMBC_LOG',
              'imbclogRequest' => $send_data,
            ]
          )
        );

        $send_data_str = implode('&', $send_data);
        
        $fsock = fsockopen(
          $remote_ip,
          80,
          $errno,
          $errstr,
          10.0
        );
        
        try {
          if (!$fsock) {
            $this->logger->error(
              sprintf('소켓오픈 실패 메세지:%s 에러코드:%d', $errstr, $errno),
              array_merge(
                $log_form,
                [
                  'keyword' => 'IMBC_LOG',
                  'duration' => $profile->end('ACTION'),
                ]
              )
            );
            // failed fsock
            return $response->withJson(['errorCode' => ERROR_SOCKER_FAILED, 'errorMessage' => '소켓오픈 실패'], 404);
          }
          else {
            fputs($fsock, "POST $remote_path HTTP/1.1\r\n");
            fputs($fsock, "Host: $remote_ip\r\n");
            fputs($fsock, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n");
            fputs($fsock, "Content-length: " . strlen($send_data_str) . "\r\n");
            fputs($fsock, "Connection:close" . "\r\n\r\n");
            fputs($fsock, $send_data_str);
          }

          $result = '';

          while (!feof($fsock)) {
            $result .= fgets($fsock, 128);
          }
          fclose($fsock);
        }
        catch (\Exception $e) {
          $this->logger->warning(
            sprintf('IMBC 로깅 실패 메세지:%s', $e->getMessage),
            array_merge(
              $log_form,
              [
                'keyword' => 'IMBC_LOG',
                'duration' => $profile->end('ACTION'),
              ]
            )
          );
        }

        $this->logger->info(
          'IMBC 로깅 응답',
          array_merge(
            $log_form,
            [
              'keyword' => 'IMBC_LOG',
              'imbclogResponse' => $result,
            ]
          )
        );

        $result = explode("\r\n\r\n", $result, 2);

        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';
      }

      $this->logger->info(
        'IMBC 로깅 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withStatus(200);
    }
  });
});
  