<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Bankmedia\Models\Purchases;
use Bankmedia\Common\Profile;

/**
 * @file routes.php
 * @ 구매목록 정보조회, 구매, 삭제 클래스
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @api GET /purchases                  구매목록 리스트 조회
 * @api GET /purchases/{id}             구매목록 재다운로드 체크
 * @api POST /purchases                 컨텐츠 구매
 * @api DELETE /purchases/{id}          구매목록 삭제
 * @api GET /purchases/paymentlog       컨텐츠 다운로드 통계
 */
$app->group('/v1', function() {
  $bbsIdxValidator = v::numeric()->length(1,10);
  $purchasesIdValidator = v::digit(',')->length(1,null);
	$categoryValidator = v::alpha('_')->noWhitespace()->length(2, 5);
	$limitValidator = v::optional(v::numeric()->between(10, 50));
  $offsetValidator = v::optional(v::numeric());
	$validators = array(
    'purchasesCheck' => array(
      'id' => $bbsIdxValidator,
    ),
    'deletePurchases' => array(
      'id' => $purchasesIdValidator,
    ),
		'purchases' => array(
      'bbs_idx' => $bbsIdxValidator,
			'category' => v::optional($categoryValidator),
			'limit' => $limitValidator,
			'offset' => $offsetValidator,
    ),
    'purchasesList' => array(
      'category' => v::optional($categoryValidator),
			'limit' => $limitValidator,
			'offset' => $offsetValidator,
    ),
  );
		
  /**
    * 현재 구매목록 리스트에 있는 컨텐츠 정보를 출력합니다.
    * 
    * @param int     bbs_idx
    * @param string  category
    * @param int     limit
    * @param int     offset
    * 
    *
    */
  $this->get('/purchases', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $params = array();
    foreach (array('bbs_idx', 'category', 'limit', 'offset') as $v) {
      if ($request->getQueryParam($v) !== null) {
        $params[$v] = $request->getQueryParam($v);
      }
    }

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');

      $this->logger->info(
        sprintf(
          '구매목록 요청 bbs_idx[%s] category[%s] limit[%d] offset[%d]',
          $params['bbs_idx'],
          $params['category'],
          $params['limit'],
          $params['offset']
        ),
        array_merge($log_form, ['keyword' => 'REQUEST'])
      );

      try {
        $profile->start('PURCHASES');
        $purchases = new Purchases($this->sdb, $this->get('settings')['site']);
        
        $params['userid'] = $token['userid'];
        $total = $purchases->countPurchases($params);
        $retPurchases = $purchases->getPurchases($params);
        
        if (count($retPurchases) == 0) {
          $this->logger->info(
            '구매 정보 없음',
            array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );

          return $response->withStatus(204);
        }

        // TODO: 단순 구매여부 체크 할때는 추가정보가 필요 없으므로 바로 리턴처리, 더 좋은 아이디어 있는지 생각해볼것.
        if ($params['bbs_idx'] !== null) {
          $this->logger->info(
            '구매목록 요청 성공',
            array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['count' => $total, 'purchases' => $retPurchases], 200);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매 정보 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        sprintf('구매 정보 조회 완료:%d', count($retPurchases)),
        array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('PURCHASES')])
      );

      $bbs_idx_list = '';
      foreach ($retPurchases as $v) {
        $bbs_idx_list = $bbs_idx_list.$v['bbs_idx'].',';
      }
      $bbs_idx_list = rtrim($bbs_idx_list, ',');
      
      $client = new Client([
        'base_uri' => 'http://api.backend-api-contents/v1/',
        'timeout'  => 2.0,
        'headers' => array(
          'Authorization' => $request->getHeader('Authorization'),
          'X-Forwarded-For' => $request->getattribute('ip_address'),
          'X-Transaction-Id' => $tid,
          'X-Transaction-Depth' => $tdepth,
          'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
        )
      ]);
          
      try {
        ($params['limit'] !== null) ? $limit = $params['limit'] : $limit = 20;

        $query = 'id=' . $bbs_idx_list . '&limit=' . $limit;

        $resp = $client->request('GET', "contents?{$query}");
        $retContents = json_decode($resp->getBody(), true);
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->warning(
          sprintf('구매 내역 컨텐츠 정보 조회 실패:%s 메세지:%s',$bbs_idx_list, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '삭제되었거나 정상적인 컨텐츠가 아닙니다.'], 404);
      }
      catch (Exception $e) {
        $this->logger->error(
          sprintf('컨텐츠 정보 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      // purchase정보 + content정보
      for ($i=0; $i<count($retContents['contents']); $i++) {
        for ($j=0; $j<count($retPurchases); $j++) {
          if ($retPurchases[$j]['bbs_idx'] == $retContents['contents'][$i]['bbs_idx']) {
            $retPurchases[$j]['content'] = $retContents['contents'][$i];
          }
        }
      }

      $this->logger->info(
        sprintf('구매정보 조회 성공:%d',count($retPurchases)),
        array_merge($log_form,['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );
      
      return $response->withJson(['count' => $total, 'purchases' => $retPurchases], 200);
    }
  })->add(new Validation($validators['purchasesList']));

  /**
   * 구매한 컨텐츠 재다운로드 체크
   * 유효기간 체크, 필터링 체크
   * 다운로드 받을 수 있는 파일리스트를 출력합니다.
   * 
   * @param int     id
   * @return array  파일 리스트
   */
  $this->get('/purchases/{id}', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $this->logger->info(
      sprintf('구매 컨텐츠 재다운로드 체크 요청 id[%d]', $args['id']),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');
      $userid = $token['userid'];
      $setting_site = $this->get('settings')['site'];
      $current_time = time();
      $filtered_files = array();

      // 구매내역 체크, 유효기간 체크
      try {
        $profile->start('PURCHASES');

        $purchases = new Purchases($this->sdb, $setting_site);
        $purchasesMdb = new Purchases($this->mdb, $setting_site);

        $ret_purchase = $purchases->getPurchases([
          'userid' => $userid,
          'id' => $args['id'],
        ]);

        if (count($ret_purchase) < 1) {
          $this->logger->info(
            sprintf('구매내역 없음:%d', $args['id']),
            array_merge($log_form,['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_NO_PURCHASES, 'errorMessage' => '구매기간이 만료되었거나 구매목록에 존재하지 않는 컨텐츠 입니다.'], 404);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매목록 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        sprintf('구매목록 조회 완료:%d', count($ret_purchase)),
        array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('PURCHASES')])
      );

      $ret_purchase = $ret_purchase[0];
      $bbs_idx = $ret_purchase['bbs_idx'];
      $copyid = $ret_purchase['copyid'];
      $download_cnt = $ret_purchase['downlod_count_m'];   //TODO:현재는 모바일 카운트만 체크
      $stream_cnt = $ret_purchase['stream_count'];
      $title = '';

      try {
        $clientContents = new Client([
          'base_uri' => 'http://api.backend-api-contents/v1/',
          'timeout'  => 4.0,
          'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
          )
        ]);
        
        /**
         * 컨텐츠 정보 요청
         */
        $resp = $clientContents->request('GET', "contents?id=$bbs_idx");
        $content = json_decode($resp->getBody(), true);
        $content = $content['contents'][0];
        $title = $content['title'];

        /**
         * 컨텐츠파일 정보 요청
         */
        $ret = $clientContents->request('GET', "contents/$bbs_idx/files");
        $ret_content_files = json_decode($ret->getBody(), true);
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->warning(
          sprintf('컨텐츠 파일정보 조회 실패:%d 메세지:%s',$bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '삭제되었거나 정상적인 컨텐츠가 아닙니다.'], 404);
      }
      catch (Exception $e) {
        $this->logger->error(
          sprintf('컨텐츠 정보 조회 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      if ($content['chkcopy'] === 'Y') {
        $downloads_exceeded = 0;

        // 1. 다운로드로드 카운트 체크
        if (in_array($copyid, $setting_site['mobileDownLimitStLimitlessCopyid'])) { // 다운로드 3회, 스트리밍 무제한
          if ($download_cnt >= 3) {
            $downloads_exceeded = 1;
          }
        }        
        else if (in_array($copyid, $setting_site['mobileLimitDnStCopyid'])) { // 다운로드 3회, 스트리밍 10회
          if (($download_cnt >= 3) || (($download_cnt + $stream_cnt) >= 10)) {
            $downloads_exceeded = 1;
          }
        }

        // 1-1. 다운로드 카운트 만료 - 구매목록 삭제
        try {
          if ($downloads_exceeded == 1) {
            $purchasesMdb->delPurchases($userid, $args['id']);

            $this->logger->info(
              '다운로드 카운트 만료',
              array_merge($log_form, ['keyword' => 'FILTER', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_DOWN_EXCEEDED, 'errorMessage' => '구매 기간이 만료되었거나 구매 목록에 존재하지 않는 컨텐츠입니다.'], 406);
          }
        }
        catch (\PDOException $e) {
          $this->logger->error(
            sprintf('구매목록 삭제 실패:%s', $e->getMessage()),
            array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
        }

        // 2. 필터링 - 해외IP체크, 모바일 다운로드 금지 체크, 제휴컨텐츠 차단파일 체크(_copy_contents)
        try {
          $clientFilters = new Client([
            'base_uri' => 'http://api.backend-api-filters/v1/',
            'timeout'  => 4.0,
            'headers' => array(
              'Authorization' => $request->getHeader('Authorization'),
              'X-Forwarded-For' => $request->getAttribute('ip_address'),
              'X-Forwarded-Country-Code' => $request->getHeader('X-Forwarded-Country-Code'),
              'X-Transaction-Id' => $tid,
              'X-Transaction-Depth' => $tdepth,
              'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
            )
          ]);

          $filter_file_list = array();
          $ret_filters = array();
          $total = count($ret_content_files['contentFiles']);

          for ($i=0; $i<$total; $i++) {
            $filter_file_list[] = $ret_content_files['contentFiles'][$i];

            if (
              (($i !== 0) && (($i % 10) === 0)) ||
              ($i === ($total - 1))
            ) {
              $resp = $clientFilters->request('GET', "filters", [
                'json' => [
                  'bbs_idx' => $bbs_idx,
                  'title' => $title,
                  'copyid' => $copyid,
                  'file_list' => $filter_file_list,
                ]
              ]);
  
              $ret_filters = json_decode($resp->getBody(), true);
              if (isset($ret_filters['errorCode']) === true) {
                $this->logger->info(
                  sprintf('컨텐츠 필터링 제한:%s', $ret_filters['errorMessage']),
                  array_merge($log_form, ['keyword' => 'FILTERS', 'duration' => $profile->end('ACTION')])
                );
  
                return $response->withJson(
                  [
                    'errorCode' => $ret_filters['errorCode'],
                    'errorMessage' => $ret_filters['errorMessage']
                  ],
                  406
                );
              }

              if (is_array($ret_filters)) {
                $filtered_files = array_merge($filtered_files, $ret_filters);
              }

              unset($filter_file_list);
              unset($ret_filters);
            }
          }
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
          $this->logger->error(
            sprintf('컨텐츠 파일 필터링 요청 실패 메세지:%s', $e->getMessage()),
            array_merge($log_form, ['keyword' => 'FILTERS', 'duration' => $profile->end('ACTION')])
          );

          // @todo errorCode 고민좀 해볼것
          return $response->withJson(['errorCode' => $e->getCode(), 'errorMessage' => '필터링 요청 실패'], $e->getCode());
        }

        $this->logger->info(
          '필터링 리스폰스 응답 메세지',
          array_merge(
            $log_form,
            [
              'keyword' => 'FILTERS',
              'filteringResponse' => $filtered_files
            ]
          )
        );

        // 3. 제휴사 paylog
        $cpr_div = $purchases->mobile_filtering_cpr_div($userid, $current_time);
        if (is_array($filtered_files) && count($filtered_files) && in_array($copyid, $setting_site['useCopyid'])) {
          try {
            $request_data = array();
            for ($i=0; $i<count($filtered_files); $i++) {
              $request_data[$i]['content_id']     = $filtered_files[$i]['content_id'];
              $request_data[$i]['ch_content_id']  = $filtered_files[$i]['ch_content_id'];
              $request_data[$i]['filtering_key']  = $filtered_files[$i]['filtering_key'];
              $request_data[$i]['paid_no']        = $cpr_div;
              $request_data[$i]['paid_price']     = 0; //재다운로드는 가격을 0으로 설정
            }
            
            $total = count($request_data);
            $request_data_arr = array();

            for ($i=0; $i<$total; $i++) {
              $request_data_arr[] = $request_data[$i];

              if (
                (($i !== 0) && (($i % 10) === 0)) ||
                ($i === ($total - 1))
              ) {
                $resp = $clientFilters->request('POST', "filters/paylog", [
                  'json' => [
                    'sell_userid' => $content['userid'],
                    'bbs_idx' => $bbs_idx,
                    'content_title' => $content['title'],
                    'now_time' => $current_time,
                    'mode' => 'DN',
                    'filter_files' => $request_data_arr,
                  ]
                ]);

                unset($request_data_arr);
              }
            }
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error(
              sprintf('페이로깅 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'PAYLOG', 'duration' => $profile->end('ACTION')])
            );
          }
        }

        // 3-1. imbc 로깅
        if ($copyid == 'imbc') {
          $imbc_send_data = array();
          $i = 0;

          foreach ($content['jehyu_contents'] as $v) {
            $imbc_send_data[$i]['contentsid'] = $v['contents_id'];
            $imbc_send_data[$i]['price'] = 0;
            $imbc_send_data[$i]['content_name'] = $v['summary'];
            $imbc_send_data[$i]['content_times'] = $v['inning'];
            
            foreach ($ret_content_files['contentFiles'] as $f) {
              if ($v['file_idx'] == $f['file_idx']) {
                $imbc_send_data[$i]['file_name'] = $f['realname'];
                break;
              }
            }
            $i++;
          }

          try {
            $total = count($imbc_send_data);
            $imbc_send_data_arr = array();

            for ($i=0; $i<$total; $i++) {
              $imbc_send_data_arr[] = $imbc_send_data[$i];

              if (
                (($i !== 0) && (($i % 10) === 0)) ||
                ($i === ($total - 1))
              ) {
                $resp = $clientFilters->request('POST', "filters/imbclog", [
                  'json' => [
                    'send_data_arr' => $imbc_send_data_arr,
                    'sellerid' => $content['userid'],
                    'buyerid' => $userid,
                    'payid' => $cpr_div,
                    'webck' => 1, //pc=0 mobile=1 (chkiphone 값)
                    'bbs_title' => $content['title'],
                    'bbs_num' => $content['bbs_idx'],
                  ],
                ]);

                unset($imbc_send_data_arr);
              }
            }
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->warning(
              sprintf('IMBC 재다운로드 로깅 실패:%d 메세지:%s', $content['bbs_idx'], $e->getMessage()),
              array_merge($log_form, ['keyword' => 'IMBC_LOG', 'duration' => $profile->end('ACTION')])
            );
          }
        }
      }

      // 4. 다운로드 카운트 증가
      $profile->start('PURCHASES_COUNT');
      try {
        $purchasesMdb->setPurchases($userid, $args['id']);

        $this->logger->info(
          '다운로드 카운트 증가 완료',
          array_merge($log_form, ['keyword' => 'PURCHASES_COUNT', 'duration' => $profile->end('PURCHASES_COUNT')])
        );
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매목록 카운팅 실패:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES_COUNT', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        sprintf('구매 컨텐츠 재다운로드 체크 성공:%d', count($ret_content_files['contentFiles'])),
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withJson($ret_content_files, 200); // 성공 파일 조회 정보 리턴
    }
  })->add(new Validation($validators['purchasesCheck']));
				
  /**
   * 컨텐츠 구매
   * 컨텐츠아이디를 파라미터로 받으면
   * 유저정보 조회, 컨텐츠정보 조회를 하고,
   * 구매 필터링, 포인트 차감, 로그등을 남깁니다.
   * 최종적으로 구매 성공하면, 해당 컨텐츠를 구매목록에 추가하고 북마크에서 삭제하게 됩니다. 
   * 
   * @param int bbs_idx
   * @return http status code
   */
  $this->post('/purchases', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $bbs_idx = $request->getParsedBodyParam('bbs_idx');

    $this->logger->info(
      sprintf('컨텐츠 구매 요청 bbs_idx[%d]', $bbs_idx),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');
      $setting_site = $this->get('settings')['site'];
      $userid = $token['userid'];
      $member = array();
      $content = array();
      $pay_info = array();
      $filtered_files = array();
      $current_time = time();
      
      // 구매 유저 정보 조회
      try {
        $clientMembers = new Client([
          'base_uri' => 'http://api.backend-api-members/v1/',
          'timeout'  => 2.0,
          'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
          )
        ]);

        $resp = $clientMembers->request('GET', "members/{$userid}");
        $member = json_decode($resp->getBody(), true);
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->error(
          sprintf('회원정보 없음:%s 메세지:%s', $userid, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'MEMBERS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_MEMBER_NOT_FOUND, 'errorMessage' => '해당 아이디는 존재하지 않습니다.'], 404);
      }
      catch (Exception $e) {
        $this->logger->error(
          sprintf('회원정보 요청 실패:%s 메세지:%s', $userid, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'MEMBERS', 'duration' => $profile->end('ACTON')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }
            
      try {
        $clientContents = new Client([
          'base_uri' => 'http://api.backend-api-contents/v1/',
          'timeout'  => 2.0,
          'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
          )
        ]);

        $resp = $clientContents->request('GET', "contents?id=$bbs_idx");
        $content = json_decode($resp->getBody(), true);
        $content = $content['contents'][0];
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->warning(
          sprintf('컨텐츠 파일정보 조회 실패:%d 메세지:%s',$bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '삭제되었거나 정상적인 컨텐츠가 아닙니다.'], 404);
      }
      catch (Exception $e) {
        $this->logger->error(
          sprintf('컨텐츠 정보 조회 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      // 1. 구매정책 - purchase 내역 확인
      try {
        $profile->start('PURCHASES');

        $purchases = new Purchases($this->sdb, $setting_site);
        $purchasesMdb = new Purchases($this->mdb, $setting_site);

        $ret = $purchases->getPurchases([
          'userid' => $member['userid'],
          'bbs_idx' => $bbs_idx,
        ]);

        if ((is_array($ret) === true) && (count($ret) > 0)) {
          $this->logger->info(
            '구매목록에 이미 존재',
            array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERORR_IS_PURCHASE, 'errorMessage' => '이미 구매한 컨텐츠입니다.'], 406);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매목록에 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        '구매목록 조회 완료',
        array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('PURCHASES')])
      );

      // 회원 level정보가 없는경우 일반 회원 처리
      if (empty($member['level'])) $member['level'] = 9;

      //관리자 회원과 일반회원 분기
      if ($member['level'] != 1) {
        // 2. 구매정책 - 결제이력 확인 (성인컨텐츠인 경우)
        if ($content['code_cate2'] == 'BD_AD') {
          try {
            $profile->start('CHARGE');

            $ret = $purchases->getAccounts($member['userid']);
            if (count($ret) < 1) {
              $this->logger->info(
                '충전 이력이 없음',
                array_merge($log_form, ['keyword' => 'CHARGE', 'duration' => $profile->end('ACTION')])
              );

              return $response->withJson(['errorCode' => ERROR_NO_ACCOUNT, 'errorMessage' => '성인 컨텐츠는 첫 충전 후 무제한 이용이 가능합니다. 포인트 충전 후 이용해 주세요.'], 406);
            }
          }
          catch (\PDOException $e) {
            $this->logger->error(
              sprintf('충전 내역 조회 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'CHARGE', 'duration' => $profile->end('ACTION')])
            );
            return $response->withJson(['errorcode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
          }

          $this->logger->info(
            '충전 내역 조회 완료',
            array_merge($log_form, ['keyword' => 'CHARGE', 'duration' => $profile->end('CHARGE')])
          );
        }

        // 3. 구매정책 - 판매자 블랙리스트 등록되어있는 유저
        try {
          $resp = $clientMembers->request('GET', "members/{$content['nickname']}/blacklist?target={$member['nickname']}");
          $blacklist = json_decode($resp->getBody(), true);

          if ($blacklist !== null) {
            $this->logger->info(
              sprintf('블랙리스트 차단 판매자:%s 차단대상:%s', $content['nickname'], $member['nickname']),
              array_merge($log_form, ['keyword' => 'BLACKLIST', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_BLACKLIST_USER, 'errorMessage' => '해당 판매회원의 차단 목록에 등록되어 다운로드 할 수 없습니다.'], 406);
          }
        }
        catch (\PDOException $e) {
          $this->logger->error(
            sprintf('판매자 블랙리스트 조회 실패 메세지:%s', $e->getMessage()),
            array_merge($log_form, ['keyword' => 'BLACKLIST', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
        }
        
        // 필터링
        if ($content['chkcopy'] == 'Y') {
          $ret = '';
          try {
            $ret = $clientContents->request('GET', "contents/{$bbs_idx}/files");
            $ret_content_files = json_decode($ret->getBody(), true);
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error(
              sprintf('컨텐츠 파일정보 조회 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
              array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorMessage' => ERROR_NO_CONTENTS, 'errorCode' => '삭제되었거나 정상적인 컨텐츠가 아닙니다.'], 404);
          }
          catch (Exception $e) {
            $this->logger->error(
              sprintf('컨텐츠 정보 조회 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
              array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
            );
    
            return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
          }

          try {
            $clientFilters = new Client([
              'base_uri' => 'http://api.backend-api-filters/v1/',
              'timeout'  => 4.0,
              'headers' => array(
                'Authorization' => $request->getHeader('Authorization'),
                'X-Forwarded-For' => $request->getAttribute('ip_address'),
                'X-Forwarded-Country-Code' => $request->getHeader('X-Forwarded-Country-Code'),
                'X-Transaction-Id' => $tid,
                'X-Transaction-Depth' => $tdepth,
                'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
              )
            ]);
            
            /**
             * 제휴컨텐츠 필터링
             * @param int     bbs_idx
             * @param string  copyid
             * @param string  title
             * @param array   file_list
             * @return paylog에 필요한 정보 
             */
            $filter_file_list = array();
            $ret_filters = array();
            $total = count($ret_content_files['contentFiles']);

            for ($i=0; $i<$total; $i++) {
              $filter_file_list[] = $ret_content_files['contentFiles'][$i];

              if ((($i !== 0) && (($i % 10) === 0))
                || ($i === ($total - 1))) {
                $resp = $clientFilters->request('GET', "filters", [
                  'json' => [
                    'bbs_idx' => $bbs_idx,
                    'title' => $content['title'],
                    'copyid' => $content['jehyu_contents'][0]['copyid'],
                    'file_list' => $filter_file_list,
                  ]
                ]);
    
                $ret_filters = json_decode($resp->getBody(), true);

                if (isset($ret_filters['errorCode']) === true) {
                  $this->logger->info(
                    sprintf('컨텐츠 필터링 제한:%s', $ret_filters['errorMessage']),
                    array_merge($log_form, ['keyword' => 'FILTERS', 'duration' => $profile->end('ACTION')])
                  );
    
                  return $response->withJson(['errorCode' => $ret_filters['errorCode'], 'errorMessage' => $ret_filters['errorMessage']], 412);
                }

                if (is_array($ret_filters)) {
                  $filtered_files = array_merge($filtered_files, $ret_filters);
                }

                unset($filter_file_list);
                unset($ret_filters);
              }
            }
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error(
              sprintf('필터링 요청 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'FILTERS', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => $e->getCode(), 'errorMessage' => '필터링 요청 실패'], 500);
          }
          catch (Exception $e) {
            $this->logger->error(
              sprintf('필터링 요청 실패:%d 메세지:%s', $bbs_idx, $e->getMessage()),
              array_merge($log_form, ['keyword' => 'FILTERS', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
          }

          $this->logger->info(
            '필터링 리스폰스 응답 메세지',
            array_merge(
              $log_form,
              [
                'keyword' => 'FILTERS',
                'filteringResponse' => $filtered_files
              ]
            )
          );
        } // chkcopy=='Y'

        if ($member['level'] >= 7) {
          $user_point = '';
          $seller_point = '';

          // point 수정 요청을 위한 'system' 권한 토큰 생성
          $send_token = array(
            'iss' => 'http://m.yesfile.com',
            'jti' => '',
            'iat' => time(),
            'exp' => time() + (60 * 1),
            'userid' => 'purchases-api',
            'role' => 'system',
            'type' => 'access',
          );
          $key = getenv('JWT_SECRET');
          $jwt = JWT::encode($send_token, $key);

          try {
            $clientMembersSystem = new Client([
              'base_uri' => 'http://api.backend-api-members/v1/',
              'timeout'  => 2.0,
              'headers' => array(
                'Authorization' => 'Bearer ' . $jwt,
                'X-Forwarded-For' => $request->getAttribute('ip_address'),
                'X-Transaction-Id' => $tid,
                'X-Transaction-Depth' => $tdepth,
                'User-Agent' => GuzzleHttp\default_user_agent() . ' Purchases/1.0',
              )
            ]);
            $resp = $clientMembersSystem->request('GET', "members/{$token['userid']}/point"); // 구매자 포인트 정보 조회
            $user_point = json_decode($resp->getBody(), true);
            $resp = $clientMembersSystem->request('GET', "members/{$content['userid']}/point"); // 판매자 포인트 정보 조회
            $seller_point = json_decode($resp->getBody(), true);

            if (($user_point === null) || ($seller_point === null)) {
              $this->logger->warning(
                '포인트 정보 조회 실패',
                array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
              );

              return $response->withJson(['errorCode' => ERROR_MEMBER_NOT_FOUND, 'errorMessage' => '포인트 정보 조회 실패'], 404);
            }
          }
          catch (\Exception $e) {
            $this->logger->error(
              sprintf('포인트 정보 조회 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
          }
                  
          // 로그에 필요한 값 초기화
          $bill_type = '';
          $pay_chk = '';
          $down_mode = '';
          
          // 결제에 필요한 값 초기화
          $packet = $user_point['packet'];    // 현금
          $point = $user_point['point'];      // 보너스 포인트
          $coupon = $user_point['coupon'];    // 쿠폰 - 다운로드받기앱에서는 사용 안함
          $price = $content['point'];         // 컨텐츠 가격
          $reward = $seller_point['reward'];
          $item = $seller_point['item'];
          $reward_per = 0;
          if ($content['chkcopy'] === 'Y') {
            $reward_per = $this->get('settings')['salePolicy']['jehyu_reward_per'];
          }
          else {
            $reward_per = $seller_point['reward_bonus'];
          }
          $update_packet = 0;
          $update_point = 0;
          $update_reward = 0;
          $update_item = 0;
          
          // 판매 로그 필요한 값
          $packet_have = $packet;
          $point_have = $point;
          $coupon_have = $coupon;
          $reward_have = $reward;
              
          if ($content['chkcopy'] === 'Y') {    //제휴 컨텐츠
            if ($packet < $price) { // 현금 부족
              $this->logger->info(
                '포인트 부족으로 구매 실패: 제휴컨텐츠',
                array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
              );

              return $response->withJson(['errorCode' => ERROR_NO_POINT_J, 'errorMessage' => '제휴컨텐츠 구매 포인트가 부족합니다.'], 412);
            }
            else {  //구매 진행
              $update_packet = $packet - $price;
              $update_reward = $reward + (($price * $reward_per) / 100);
              $bill_type = 'J';
              $pay_chk = 'CP';
              $down_mode = 'BBS_CP';
              $packet_have = $update_packet;
            }
          }
          else {  // 일반 컨텐츠
            //정액제
            if ($user_point['fix_end'] > time()) {
              $bill_type = 'S';
              $pay_chk = 'F';
              $down_mode = 'BBS_F';
              $update_item = $item + 1;// 판매자에게 아이템 포인트 +1
            }
            else { // 보너스포인트 or 일반결제
              if (($point + $packet) < $price) { // 포인트+현금 부족
                $this->logger->info(
                  '포인트 부족으로 구매 실패: 일반컨텐츠',
                  array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
                );

                return $response->withJson(['errorCode' => ERROR_NO_POINT_C, 'errorMessage' => '일반컨텐츠 구매 포인트가 부족합니다.'], 412);
              }
              else {
                if ($point > $price) {   // 포인트로만 결제 할 경우
                  $update_point = $point - $price; // 구매자 포인트 차감
                  $point_have = $update_point;
                  $update_item = $item + 1; // 판매자 아이템 포인트 증감
                  $bill_type = 'B';
                  $pay_chk = 'B';
                  $down_mode = 'BBS_B';
                }
                else {  // 포인트+현금 or 현금으로만 결제 할 경우
                  if ($point > 0) { // 포인트+현금
                    $na_point = $price - $point; // $na_point는 보너스 포인트를 깎고 난 후 현금 결제해야 하는 금액
                    $update_point = $point - ($price - $na_point); //구매자 보너스포인트 차감
                    $update_packet = $packet - $na_point; //구매자 현금 차감
                    $update_reward = $reward + (($na_point * $reward_per) / 100); // 현금 결제한 만큼의 판매자 리워드 보상
                    $bill_type = 'A';
                    $pay_chk = 'S';
                    $down_mode = 'BBS_S';
                    $point_have = $update_point;
                  }
                  else {  // 현금 결제
                    $update_packet = $packet - $price; //현금 차감
                    $update_reward = $reward + (($price * $reward_per) / 100); //판매자 리워드
                    $bill_type = 'A';
                    $pay_chk = 'P';
                    $down_mode = 'BBS_P';
                  }
                  $packet_have = $update_packet;
                  $reward_have = $update_reward;
                }
              }
            }
          }

          // @todo 포인트 변경에 멱등성 개념을 적용해야 함.
          // @todo PUT 요청 성공 실패 여부 판단 고민
          try {
            if ($update_packet > 0) {
              $this->logger->info(
                sprintf('packet 변경 요청:%s %01.2f -> %01.2f', $token['userid'], $packet, $update_packet),
                array_merge($log_form, ['keyword' => 'POINT'])
              );

              $resp = $clientMembersSystem->request('PUT', "members/{$token['userid']}/point", [
                'json' => [
                  'point_name' => 'packet',
                  'point' => $update_packet,
                ]
              ]);
            }
            
            if ($update_point > 0) {
              $this->logger->info(
                sprintf('point 변경 요청:%s %01.2f -> %01.2f', $token['userid'], $point, $update_point),
                array_merge($log_form, ['keyword' => 'POINT'])
              );

              $resp = $clientMembersSystem->request('PUT', "members/{$token['userid']}/point", [
                'json' => [
                  'point_name' => 'point',
                  'point' => $update_point,
                ]
              ]);
            }
            
            // 모바일에서 제휴컨텐츠는 리워드를 제공하고있지 않습니다.
            if (($update_reward > 0) && ($content['chkcopy'] !== 'Y')) {
              $this->logger->info(
                sprintf('reward 변경 요청:%s %01.2f -> %01.2f', $content['userid'], $reward, $update_reward),
                array_merge($log_form, ['keyword' => 'POINT'])
              );

              $resp = $clientMembersSystem->request('PUT', "members/{$content['userid']}/point", [
                'json' => [
                  'point_name' => 'reward',
                  'point' => $update_reward,
                ]
              ]);
            }
            
            if ($update_item > 0) {
              $this->logger->info(
                sprintf('item 변경 요청:%s %01.2f -> %01.2f', $content['userid'], $item, $update_item),
                array_merge($log_form, ['keyword' => 'POINT'])
              );

              $resp = $clientMembersSystem->request('PUT', "members/{$content['userid']}/point", [
                'json' => [
                  'point_name' => 'item',
                  'point' => $update_item,
                ]
              ]);
            }
          }
          catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->error(
              sprintf('포인트 수정 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
            );

            if ($e->getCode() == 403) {
              return $response->withJson(array('errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '인가 실패'), 403);
            }else if ($e->getCode() == 404) {
              return $response->withJson(array('errorCode' => ERROR_SET_POINT, 'errorMessage' => '포인트 수정 실패'), 404);
            }else if ($e->getCode() == 412) {
              return $response->withJson(array('errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => 'validation 실패'), 412);
            }
          }
          catch (Exception $e) {
            $this->logger->error(
              sprintf('포인트 수정 실패 메세지:%s', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'POINTS', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], $e->getCode());
          }

          // 판매 로그 - 현금결제, 제휴결제만 로깅 (log_seller)
          if ($pay_chk == 'P' || $pay_chk == 'CP') {
            try {
              $profile->start('SAIL_LOG');

              $log_options = array(
                'userid' => $content['userid'],
                'recv_userid' => $member['userid'],
                'recv_nickname' => $member['nickname'],
                'bbs_idx' => $bbs_idx,
                'title' => $content['title'],
                'down_mode' => $down_mode,
                'packet_use' => $packet - $packet_have,
                'packet_save' => (($price * $reward_per) / 100),
                'packet_have' => $packet_have,
                'point_have' => $point_have,
                'coupon_have' => $coupon_have,
                'regdate' => $current_time,
                'copyid' => '',
                'contents_id' => '',
                'copy_idx' => 0,
              );

              // 제휴컨텐츠면 정보를 추가입력
              if ($content['chkcopy'] == 'Y') {
                $log_options['copyid'] = $content['jehyu_contents'][0]['copyid'];
                $log_options['contents_id'] = $content['jehyu_contents'][0]['contents_id'];
                $log_options['copy_idx'] = $content['jehyu_contents'][0]['idx'];
              }

              $purchasesMdb->saleLog($log_options);

              $this->logger->info(
                sprintf('판매로깅 완료:%d 판매방식:%s', $bbs_idx, $pay_chk),
                array_merge($log_form, ['keyword' => 'SAIL_LOG', 'duration' => $profile->end('SAIL_LOG')])
              );
            }
            catch (\PDOException $e) {
              $this->logger->error(
                sprintf('판매자 판매로깅 실패 메세지:%s', $e->getMessage()),
                array_merge($log_form, ['keyword' => 'SAIL_LOG', 'duration' => $profile->end('ACTION')])
              );
            }
          }else {
            $this->logger->info(
              sprintf('판매로깅 미해당 컨텐츠:%d 판매방식:%s', $bbs_idx, $pay_chk),
              array_merge($log_form, ['keyword' => 'SAIL_LOG'])
            );
          }
          
          // 제휴 컨텐츠 로깅
          if ($content['chkcopy'] == 'Y') {
            $cpr_div = $purchases->mobile_filtering_cpr_div($member['userid'], $current_time);
            $imbc_send_data = array();

            // paylog
            if (is_array($filtered_files) && count($filtered_files) && in_array($content['jehyu_contents'][0]['copyid'], $setting_site['useCopyid'])) {
              try {
                $request_data = array();

                for ($i=0; $i<count($filtered_files); $i++) {
                  $request_data[$i]['content_id']     = $filtered_files[$i]['content_id'];
                  $request_data[$i]['ch_content_id']  = $filtered_files[$i]['ch_content_id'];
                  $request_data[$i]['filtering_key']  = $filtered_files[$i]['filtering_key'];
                  $request_data[$i]['paid_no']        = $cpr_div;
                  if (count($content['jehyu_contents']) > 0) {
                    foreach ($content['jehyu_contents'] as $v) {
                      if ($filtered_files[$i]['file_idx'] == $v['file_idx']) {
                        $request_data[$i]['paid_price'] = $v['set_point'];
                        break;
                      }
                    }
                  }
                  else {
                    // TODO: 예외상황인 경우 기존코드에서 파일가격 설정 부분에 컨텐츠 가격을 넣고있었음. 체크사항
                    $request_data[$i]['paid_price'] = $content['point'];
                  }
                }
                
                $this->logger->info(
                  sprintf('제휴 컨텐츠 페이로깅:%d', $bbs_idx),
                  array_merge(
                    $log_form,
                    [
                      'keyword' => 'PAYLOG',
                      'paylogRequest' => $request_data,
                    ]
                  )
                );

                $total = count($request_data);
                $request_data_arr = array();

                for ($i=0; $i<$total; $i++) {
                  $request_data_arr[] = $request_data[$i];

                  if (
                    (($i !== 0) && (($i % 10) === 0)) ||
                    ($i === ($total - 1))
                  ) {
                    $resp = $clientFilters->request('POST', "filters/paylog", [
                      'json' => [
                        'sell_userid' => $content['userid'],
                        'bbs_idx' => $bbs_idx,
                        'content_title' => $content['title'],
                        'now_time' => $current_time,
                        'mode' => 'PM',
                        'filter_files' => $request_data_arr,
                      ]
                    ]);

                    unset($request_data_arr);
                  }
                }
              }
              catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->error(
                  sprintf('페이로깅 실패 메세지:%d :%s',$bbs_idx, $e->getMessage()),
                  array_merge($log_form, ['keyword' => 'PAYLOG', 'duration' => $profile->end('ACTION')])
                );
              }
            }

            // 제휴판매 로깅 (log_copyright)
            try {
              $profile->start('COPYRIGHT_LOG');

              $log_cooperation_options = array(
                'sellerid' => $content['userid'],
                'copyid' => $content['jehyu_contents'][0]['copyid'],
                'recv_userid' => $member['userid'],
                'recv_nickname' => $member['nickname'],
                'cp_userid' => '',
                'bbs_idx' => $bbs_idx,
                'down_mode' => $down_mode,
                'regdate' => $current_time,
                'cpr_div' => $cpr_div,
              );

              $i = 0;
              foreach ($content['jehyu_contents'] as $v) {
                $log_cooperation_options['title'] = $v['summary'];
                $log_cooperation_options['contents_id'] = $v['contents_id'];
                $log_cooperation_options['packet_use'] = $v['set_point'];
                $log_cooperation_options['packet_save'] = (($v['set_point'] * $reward_per) / 100);

                $purchasesMdb->cooperationLog($log_cooperation_options);
                
                if ($v['copyid'] == 'imbc') {
                  $imbc_send_data[$i]['contentsid'] = $v['contents_id'];
                  $imbc_send_data[$i]['price'] = $v['set_point'];
                  $imbc_send_data[$i]['content_name'] = $v['summary'];
                  $imbc_send_data[$i]['content_times'] = $v['inning'];
                  
                  foreach ($ret_content_files['contentFiles'] as $f) {
                    if ($v['file_idx'] == $f['file_idx']) {
                      $imbc_send_data[$i]['file_name'] = $f['realname'];
                      break;
                    }
                  }
                  $i++;                
                }
              }
            }
            catch (\PDOException $e) {
              $this->logger->error(
                sprintf('제휴판매 로깅 실패 메세지:%s', $e->getMessage()),
                array_merge($log_form, ['keyword' => 'COPYRIGHT_LOG', 'duration' => $profile->end('ACTION')])
              );
            }

            $this->logger->info(
              '제휴판매 로깅 완료',
              array_merge($log_form, ['keyword' => 'COPYRIGHT_LOG', 'duration' => $profile->end('COPYRIGHT_LOG')])
            );

            // imbc 로깅
            if (($content['jehyu_contents'][0]['copyid'] == 'imbc') && count($imbc_send_data)) {
              try {
                $total = count($imbc_send_data);
                $imbc_request_data = array();

                for ($i=0; $i<$total; $i++) {
                  $imbc_request_data[] = $imbc_send_data[$i];
                  
                  if (
                    (($i !== 0) && (($i % 10) === 0)) ||
                    ($i === ($total - 1))
                  ) {
                    $resp = $clientFilters->request('POST', "filters/imbclog", [
                      'json' => [
                        'send_data_arr' => $imbc_send_data,
                        'sellerid' => $content['userid'],
                        'buyerid' => $member['userid'],
                        'payid' => $cpr_div,
                        'webck' => 1, //pc=0 mobile=1 (chkiphone 값)
                        'bbs_title' => $content['title'],
                        'bbs_num' => $content['bbs_idx'],
                      ]
                    ]);

                    unset($imbc_request_data);
                  }
                }
              }
              catch (\GuzzleHttp\Exception\ClientException $e) {
                $this->logger->warning(
                  sprintf('IMBC 구매 로깅 실패:%d 메세지:%s', $content['bbs_idx'], $e->getMessage()),
                  array_merge(
                    $log_form,
                    [
                      'keyword' => 'IMBC_LOG',
                      'duration' => $profile->end('ACTION'),
                    ]
                  )
                );
              }
            } // imbc 로깅
          } // $content['chkcopy'] = 'Y'
        } // $member['level'] >= 7
      } // $member['level'] != 1

      /**
       * 구매목록 추가
       */
      # J:제휴포인트,P:포인트,B:보너스,A:보너스+포인트,C:쿠폰,D:주간정액,N:야간정액,S:스페셜정액
      if ($member['level'] == 1) {
        $bill_type = 'S';
      }

      try {
        $pay_info = array(
          'bbs_idx' => $bbs_idx,
          'bill_type' => $bill_type,
          'point_use' => $point - $point_have,
          'packet_use' => $packet - $packet_have,
          'title' => $content['title'],
          'category' => $content['code'],
          'size' => $content['size'],
          'grade' => $content['uploader_grade'],
          'userid' => $member['userid'],
          'nickname' => $member['nickname'],
          'seller_id' => $content['userid'],
          'seller_nick' => $content['nickname'],
          'regdate' => $current_time,
          'copyid' => $content['jehyu_contents'][0]['copyid'],
          'client_ip' => $request->getAttribute('ip_address'),
        );

        $purchasesMdb->putPurchase($pay_info);
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매목록 추가 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES_ADD', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        sprintf('구매 성공:%s 컨텐츠:%d', $userid, $bbs_idx),
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );
    }
  })->add(new Validation($validators['purchases']));

  /**
   * 구매목록 삭제
   * 
   * @param int id
   * 
   * @return status code
   */
  $this->delete('/purchases/{id}', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $this->logger->info(
      sprintf('구매목록 삭제 요청 id[%d]', $args['id']),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');

      try {
        $purchases = new Purchases($this->mdb, $this->get('settings')['site']);
        $ret = $purchases->delPurchases($token['userid'], $args['id']);
        if ($ret === false) {
          $this->logger->warning(
            sprintf('구매목록 삭제 실패:%s', $args['id']),
            array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_NO_DELETE_PURCHASES, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 404);
        }

        $this->logger->info(
          '구매목록 삭제 성공',
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );

        return $response->withStatus(200);
      }
      catch (\PDOException $e)
      {
        $this->logger->error(
          sprintf('구매목록 삭제 실패:%s 메세지:%s',$args['id'], $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }
    }
  })->add(new Validation($validators['deletePurchases']));

  /**
   * 다운로드 통계 카운트 입력 및 업데이트
   * 
   * @param payment_place   다운로드 구매처
   * @param use_point       다운로드 받은 컨텐츠 가격
   */
  $this->put('/purchases/paymentlog', function(Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $params = array();
    foreach (array('payment_place', 'use_point') as $v) {
      $params[$v] = $request->getParsedBodyParam($v);
    }

    $this->logger->info(
      sprintf('컨텐츠 다운로드 통계 카운트 업데이트 요청 payment_place[%s], use_point[%d]', $params['payment_place'], $params['use_point']),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      try {
        $purchases = new Purchases($this->mdb, $this->get('settings')['site']);
        if ($purchases->updatePurchasesCount($params['payment_place'], intval($params['use_point']))) {
          return $response->withStatus(200);
        }
        else {
          return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 404);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('다운로드 통계 카운트 업데이트 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }
    }
  });
});