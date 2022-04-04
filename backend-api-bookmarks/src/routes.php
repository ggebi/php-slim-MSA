<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use GuzzleHttp\Client;
use Bankmedia\Models\Bookmarks;
use Bankmedia\Common\Profile;

/**
* @file routes.php
* @brief bookmarks API
* @author 주형우 (jhwmon@bankmedia.co.kr)
* 
* @api GET /bookmarks          북마크(찜목록) 조회
* @api DELETE /bookmarks/{id}  북마크(찜목록) 삭제 
* @api POST /bookmarks         북마크(찜목록) 추가
*/
$app->group('/v1', function() {
  $bookmarkIdValidator = v::digit(',')->length(1,null);
  $contentIdValidator = v::numeric()->length(1,10);
  $categoryValidator = v::alpha('_')->noWhitespace()->length(2, 5);
  $limitValidator = v::optional(v::numeric()->between(10, 50));
  $offsetValidator = v::optional(v::numeric());
  $validators = array(
    'bookmark' => array(
      'id' => $bookmarkIdValidator,
    ),
    'bookmarks' => array(
      'category' => v::optional($categoryValidator),
      'limit' => $limitValidator,
      'offset' => $offsetValidator,
    ),
    'putBookmarks' => array(
      'bbs_idx' => $contentIdValidator,
    ),
  );
  
  /**
   * 북마크 리스트 조회 (찜목록)
   * 
   * @param string    id
   * @param string    category
   * @param int       limit
   * @param int       offset
   * 
   * @return array    (bookmarks + content)
   *   {
   *     "count": 2,
   *     "bookmarks": [
   *       {
   *         "bbs_idx": "1",
   *         "code": "BD_MV_04",
   *         "content": {
   *           "cate1_kr": "영화",
   *           "cate2_kr": "최신/미개봉",
   *           ...
   *         },
   *         "idx": "1",
   *         ...
   *       },
   *       {
   *         "bbs_idx": "2",
   *         ...
   *       },
   *     ],
   *   }
   * 
   */
  $this->get('/bookmarks', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];
    
    $params = array();
    foreach (array('category', 'limit', 'offset') as $v) {
      if ($request->getQueryParam($v) !== null) {
        $params[$v] = $request->getQueryParam($v);
      }
    }

    $this->logger->info(
      sprintf(
        '북마크 리스트 조회 요청 category[%s] limit[%d] offset[%d]',
        $params['category'],
        $params['limit'],
        $params['offset']
      ),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      $token = $request->getAttribute('token');
      $params['userid'] = $token['userid'];

      try {
        $profile->start('BOOKMARKS');

        $bookmarks = new Bookmarks($this->sdb);

        $total = $bookmarks->countBookmarks($params);
        $retBookmarks = $bookmarks->getBookmarks($params);
        
        if (($total === 0) || (count($retBookmarks) == 0)) {
          $this->logger->info(
            '북마크 리스트 조회 결과 없음',
            array_merge($log_form, ['keyword' => 'BOOKMARKS', 'duration' => $profile->end('ACTION')])
          );

          return $response->withStatus(204);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('북마크 리스트 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'BOOKMARKS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        sprintf('북마크 리스트 조회 완료:%d', count($retBookmarks)),
        array_merge($log_form, ['keyword' => 'BOOKMARKS', 'duration' => $profile->end('BOOKMARKS')])
      );
      
      $bbs_idx_list = '';
      foreach($retBookmarks as $v) {
        $bbs_idx_list = $bbs_idx_list.$v['bbs_idx'].',';
      }
      $bbs_idx_list = rtrim($bbs_idx_list, ',');
          
      try {
        $client = new Client([
          'base_uri' => 'http://api.backend-api-contents/v1/',
          'timeout'  => 2.0,
          'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Bookmarks/1.0',
          )
        ]);
        
        ($params['limit'] !== null) ? $limit = $params['limit'] : $limit = 20;
        $query = 'id=' . $bbs_idx_list . '&limit=' . $limit;
        // ($params['category'] !== null) and $query .= '&category='.$params['category'];

        $resp = $client->request('GET', "contents?{$query}");
        $retContents = json_decode($resp->getBody(), true);
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->warning(
          sprintf('컨텐츠 정보 없음%s 메세지:%s',$bbs_idx_list, $e->getMessage()),
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

      /**
       * bookmark lis nomalization
       * TODO: 향후에 로직 개선 할 것.
       */
      // bookmark정보 + content정보
      for ($i=0; $i<count($retContents['contents']); $i++) {
        for ($j=0; $j<count($retBookmarks); $j++) {
          if ($retBookmarks[$j]['bbs_idx'] == $retContents['contents'][$i]['bbs_idx']) {
            $retBookmarks[$j]['content'] = $retContents['contents'][$i];

            // 불필요한 데이터 제거
            unset($retBookmarks[$j]['userid']);
          }
        }
      }
      
      $this->logger->info(
        sprintf('북마크 리스트 조회 성공:%d', count($retBookmarks)),
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withJson(['count' => $total, 'bookmarks' => $retBookmarks], 200); //성공
    }
  })->add(new Validation($validators['bookmarks']));
      
  /**
   * 북마크 목록 삭제 (찜목록)
   * 
   * @param string id (1,2,3,...)
   * 
   * @return status code
   */
  $this->delete('/bookmarks/{id}', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];
    $idx_list = $args['id'];

    $this->logger->info(
      sprintf('북마크 삭제 요청 id[%s]', $idx_list),
      array_merge($log_form, ['keyowrd' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => '잘못된 정보'], 412);
    }
    else {
      $token = $request->getAttribute('token');

      try
      {
        $bookmarks = new Bookmarks($this->mdb);
        $ret = $bookmarks->delBookmarks($token['userid'], $idx_list);
        if (false === $ret) {
          $this->logger->info(
            '북마크 삭제 실패',
            array_merge($log_form, ['keyword' => 'BOOKMARKS', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(array('errorCode' => ERROR_NO_DELETE_BOOKMARKS, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 404);
        }
        
        $this->logger->info(
          '북마크 삭제 성공',
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );

        return $response->withStatus(200);
      }
      catch (\PDOException $e)
      {
        $this->logger->error (
          sprintf('북마크 삭제 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'BOOKMARKS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
      }
    }
  })->add(new Validation($validators['bookmark']));
  
  /**
   * 북마크 목록 추가 (테스트용 임시 추가)
   * 
   * @param int bbs_idx
   * 
   * @return status code
   * TODO: 테스트용 API라서 로깅 안남겨놓음
  */
  $this->post('/bookmarks', function (Request $request, Response $response, array $args) {
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
      $bbs_idx = $request->getParsedBodyParam('bbs_idx');
      
      if (isset($token['type']) && ($token['type'] !== 'access')) {
        $this->logger->warning(
          '인가 실패:잘못 된 토큰',
          array_merge($log_form, ['keyword' => 'TOKEN', 'duration' => $profile->end('ACTION')])
        );
        
        return $response->withJson(['errorCode' => ERROR_TOKEN, 'errorMessage' => '잘못 된 토큰'], 403);
      }

      try {
        $client = new Client([
            'base_uri' => 'http://api.backend-api-contents/v1/',
            'timeout'  => 2.0,
            'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Bookmarks/1.0',
          )
        ]);
        $resp = $client->request('GET', "contents?id={$bbs_idx}");
        $retContent = json_decode($resp->getBody(), true);

        if ( ($retContent === false) || $retContent === null ) {
          return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '컨텐츠 정보 없음'], 404);
        }
      }
      catch(\Exception $e) {
        return $response->withJson(array('errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()), 500);
      }
      
      $retContent = $retContent['contents'][0];
      $options = array (
        'bbs_idx' => $bbs_idx,
        'title' => $retContent['title'],
        'size' => $retContent['m_size'],
        'code' => $retContent['code'],
        'uploader' => $retContent['userid'],
        'chkiphone' => $retContent['chkiphone'],
      );
      
      try {
        $bookmarks = new Bookmarks($this->mdb);
        $ret = $bookmarks->countBookmarks(array('bbs_idx' => $bbs_idx, 'userid' => $token['userid']));
        if ($ret > 0) {
          return $response->withJson(['errorCode' => ERROR_DUPLICATED, 'errorMessage' => '중복된 요청'], 400);
        }
        
        $bookmarks->putBookmarks($token['userid'], $options);
        
        return $response->withStatus(200);
      }
      catch (\PDOException $e) {
        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => $e->getMessage()), 500);
      }
    }
  })->add(new Validation($validators['putBookmarks']));
});