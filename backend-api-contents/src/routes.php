<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use GuzzleHttp\Client;
use Bankmedia\Models\Contents;
use Bankmedia\Common\Profile;
use Urlsign\Urlsign;

/**
 * @file routes.php
 * @brief contents API
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @api GET /contents                       컨텐츠 리스트 조회
 * @api GET /contents/{id}/files            컨텐츠 한건에 대한 파일 리스트 조회
 * @api GET /contents/downurl   파일 다운로드 URL 요청
 * @api GET /contents/series/{id}
 * @api GET /contents/recommends/movie
 */
$app->group('/v1', function() {
  $boardIdxValidator = v::digit(',')->length(1,null);
  $categoryValidator = v::alpha('_')->noWhitespace()->length(2, 5);
  $limitValidator = v::optional(v::numeric()->between(10, 50));
  $offsetValidator = v::optional(v::numeric()->min(0));
  $sortValidator = v::optional(v::numeric()->min(0)->max(1));
  $validators = array(
    'contents' => array(
      'id' => $boardIdxValidator,
      'limit' => $limitValidator,
      'offset' => $offsetValidator,
      'category' => v::optional($categoryValidator),
    ),
    'recommends' => array(
      'limit' => $limitValidator,
      'offset' => $offsetValidator,
    ),
    'series' => array(
      'id' => $boardIdxValidator,
      'limit' => $limitValidator,
      'page' => $offsetValidator,
      'sort' => $sortValidator,
      'is_series' => $sortValidator,
    ),
    'downurl' => array(
      'id' => $boardIdxValidator,
      'file_idx' => $boardIdxValidator,
    ),
  );
  
  /**
  * @brief 컨텐츠 정보 조회
  * 
  * @param string    id
  * @param string    category
  * @param int       limit
  * @param int       offset
  * 
  * @return array
  * {
  *      "contents": [
  *          {
  *              "cate1_kr": "영화",
  *              "cate2_kr": "최신/미개봉",
  *              ...
  *          },
  *          {
  *              "cate1_kr": "영화",
  *              "cate2_kr": "최신/미개봉",
  *              "jehyu_contents": [
  *                  {
  *                      "bbs_idx": "2",
  *                      "contents_id": "N1004035",
  *                      "copyid": "cjenm",
  *                      ...
  *                  },
  *              ],
  *              "m_size": "10800000000",
  *              ...
  *          },
  *      ],
  *      "count":2
  *  }
  */
  $this->get('/contents', function (Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $params = array();
    foreach (array('id', 'category', 'limit', 'offset') as $v) {
      if (($request->getQueryParam($v)) !== null) {
        $params[$v] = $request->getQueryParam($v);
      }
    }

    $this->logger->info(
      sprintf(
        '컨텐츠 리스트 요청 id[%s] category[%s] limit[%d] offset[%d]',
        $params['id'],
        $params['category'],
        $params['limit'],
        $params['offset']
      ),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation error',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      try {
        $profile->start('CONTENTS');
        
        $contents = new Contents($this->sdb, $this->get('settings')['site']);

        $total = $contents->countContents($params);
        $retContents = $contents->getContents($params);
        
        if (($total === 0) || (count($retContents) == 0)) {
          $this->logger->warning(
            '컨텐츠 정보 조회 결과 없음',
            array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
          );
                
          return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '컨텐츠 정보 없음'], 404);
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('컨텐츠 정보 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );
                  
        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 에러'), 500);
      }
                
      $this->logger->info(
        sprintf('컨텐츠 정보 조회 완료:%d', count($retContents)),
        array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('CONTENTS')])
      );
                    
      $profile->start('ADD_INFO');
      
      $ret = array();
      $i = 0;
      
      try {
        foreach ($retContents as $v) {
          $category = $v['cate1_kr']." > ".$v['cate2_kr'];
          unset($v['cate1_kr']);
          unset($v['cate2_kr']);

          $ret[$i] = $v;
          $ret[$i]['file_cnt'] = $contents->countContentFiles($v['bbs_idx']);
          $comment = $contents->getContentScore($v['bbs_idx']);
          $ret[$i]['comment_score'] = $comment['score'];
          $ret[$i]['comment_count'] = $comment['total'];
          $ret[$i]['point'] = $contents->getContentPrice($v);
          $ret[$i]['title_img'] = $contents->getContentImage($v);
          $ret[$i]['category'] = $category;
          if ($v['chkcopy'] === 'Y') {
            $ret[$i]['jehyu_contents'] = $contents->getContentJehyu($v['bbs_idx']);
          }
          
          $i++;
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('컨텐츠 추가 정보 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'ADD_INFO', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 에러'), 500);
      }
          
      $this->logger->info(
        sprintf('컨텐츠 리스트 추가정보 조회 완료:%d', count($ret)),
        array_merge($log_form, ['keyword' => 'ADD_INFO', 'duration' => $profile->end('ADD_INFO')])
      );
              
      $this->logger->info(
        '컨텐츠 리스트 조회 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );
          
      return $response->withJson(['count' => $total, 'contents' => $ret], 200); //성공
    }
  })->add(new Validation($validators['contents']));
                                
  /**
  * 컨텐츠의 파일 리스트 요청 서비스
  * 인코딩파일정보 + 컨텐츠의 파일정보 + 파일 영상정보
  * 
  * @param int id    컨텐츠 번호
  * @return array
  *   {
  *     "contentFiles": [
  *       {
  *         "category": "BD_MV",
  *         "fileidx": "1",
  *         "play_time_int": "3860",
  *         "realname": "The.Godfather.1972.mp4",
  *         "size": "1.8G",
  *         "title_img": "http://testimage.yesfile.com/data/2018/05/21ws_55296371_300_0_25.jpg"
  *       },
  *       {
  *         "category": "BD_MV",
  *         "fileidx": "2",
  *         "play_time_int": "3862",
  *         "realname": "The.Godmother.1972.mp4",
  *         "size": "5.5G",
  *         "title_img": "http://testimage.yesfile.com/data/2018/05/21ws_55296371_300_0_25.jpg"
  *       },
  *     ],
  *     "count": 3
  *   }
  */
  $this->get('/contents/{id}/files', function(Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');      
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $this->logger->info(
      sprintf(
        '컨텐츠 파일 리스트 요청 id[%d]',
        $args['id']
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
      try {
        $contents = new Contents($this->sdb, $this->get('settings')['site']);

        $total = $contents->countContentFiles($args['id']);
        $encodeFiles = $contents->getEncodeFiles($args);
        
        $file_list = '';
        foreach ($encodeFiles as $v) {
          $file_list .= $v['file_idx'].',';
        }
        $file_list = rtrim($file_list, ',');
        
        $mmsvFiles = $contents->getFiles(array('file_idx_list' => $file_list)); //파일 + 파일재생시간 정보
        $data = $contents->getContents($args);
        $img = $contents->getContentImage($data[0]); //썸네일 이미지
        $category = $data[0]['code_cate2'];//카테고리
        
        $result = array();
        for ($i = 0; $i < count($encodeFiles); $i++) {
          $result[$i]['file_idx'] = $encodeFiles[$i]['file_idx'];
          
          $rename = '';
          $rename_arr = explode(".", $encodeFiles[$i]['realname']);
          for ($j=0; $j<count($rename_arr)-1; $j++) {
            $rename .= $rename_arr[$j].".";
          }

          $result[$i]['realname'] = $rename.'mp4';
          $result[$i]['size'] = $encodeFiles[$i]['size'];
          $result[$i]['title_img'] = $img;
          $result[$i]['category'] = $category;
          $result[$i]['category_int'] = $contents->category_choice_shot($category);
          $result[$i]['play_time_int'] = $mmsvFiles[$i]['play_time'];
          $result[$i]['mmsv_size'] = $mmsvFiles[$i]['size'];
        }
        
        $this->logger->info(
          sprintf('컨텐츠 파일 리스트 조회 성공:%d', count($result)),
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson(['contentFiles' => $result, 'count' => $total], 200);
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('컨텐츠 파일 리스트 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson(['errorcode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 에러'], 500);
      }
    }
  })->add(new Validation($boardIdxValidator));
  
  /**
  * 다운로드 URL 요청 서비스
  * 
  * @param int id
  * @param int file_idx
  * 
  * @return array
  *   {
  *     "down_url": "url",
  *   }
  */
  $this->get('/contents/downurl', function(Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    $options['id'] = $request->getQueryParam('id');
    $options['file_idx'] = $request->getQueryParam('file_idx');

    $this->logger->info(
      sprintf(
        '다운로드 URL 요청 id[%d] file_idx[%d]',
        $options['id'],
        $options['file_idx']
      ),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    );

    if ($request->getAttribute('has_errors')) {
      $data = array(
        'errorCode' => ERROR_INVALID_PARAM,
        'errorMessage' => $request->getAttribute('errors'),
      );
      return $response->withJson($data, 412);
    }
    else {
      try {
        $client = new Client([
          'base_uri' => 'http://api.backend-api-purchases/v1/',
          'timeout' => 2.0,
          'headers' => array(
            'Authorization' => $request->getHeader('Authorization'),
            'X-Forwarded-For' => $request->getAttribute('ip_address'),
            'X-Transaction-Id' => $tid,
            'X-Transaction-Depth' => $tdepth,
            'User-Agent' => GuzzleHttp\default_user_agent() . ' Contents/1.0',
          )
        ]);

        $resp = $client->request('GET', "purchases?bbs_idx={$options['id']}");
        $ret = json_decode($resp->getBody(), true);
        
        if ($ret['purchases'] === null) {
          $this->logger->info(
            '구매내역 없음',
            array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
          );
              
          return $response->withJson(['errorCode' => ERROR_NO_PURCHASES, 'errorMessage' => '구매기간이 만료되었거나 구매목록에 존재하지 않는 컨텐츠 입니다.'], 404);
        }                
      }
      catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->logger->error(
          sprintf('구매내역 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson( ['errorCode' => ERROR_NO_PURCHASES, 'errorMessage' => '구매내역 요청 에러'], 404 );
      }
      catch (\Exception $e) {
        $this->logger->error(
          sprintf('구매내역 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'PURCHASES', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson(['errorCode' => $e->getCode(), 'errorMessage' => 'db 에러'], 500);
      }
      
      // 구매한 컨텐츠의 정보 조회
      try {
        $profile->start('CONTENTS');
        
        $contents = new Contents($this->sdb, $this->get('settings')['site']);
        $encode_file = $contents->getEncodeFiles($options);
        if (count($encode_file) < 1) {
          $this->logger->warning(
            '구매한 컨텐츠 정보 없음',
            array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
          );
          
          return $response->withJson(['errorCode' => ERROR_NO_CONTENTS, 'errorMessage' => '컨텐츠 정보 없음'], 404);
        }
        
        $encode_file = $encode_file[0]; //인코딩 된 파일들의 정보 중 한 파일의 정보만 필요
        
        $encode_file['temp_name'] = trim($encode_file['temp_name']);
        if (!isset($encode_file['temp_name']) || (strlen($encode_file['temp_name']) < 34)) {
          $this->logger->info(
            '구매한 컨텐츠가 모바일 컨텐츠가 아님',
            array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
          );
              
          return $response->withJson(array('errorCode' => ERROR_NO_MOBILE, 'errorMessage' => '모바일 컨텐츠가 아님'), 406);
        }
        
        $this->logger->info(
          '구매한 컨텐츠 정보 조회 완료',
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('CONTENTS')])
        );
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('구매한 컨텐츠 정보 조회 실패 메세지:%s', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );
            
        return $response->withJson(['errorcode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => $e->getMessage], 500);
      }
      
      //url 요청
      $reqHost = $this->get('settings')['urlsign_info']['requestHost'];
      $reqPath = "{$encode_file['temp_name'][0]}/{$encode_file['temp_name'][1]}/{$encode_file['temp_name']}.mp4";
      $reqUrl = "http://{$reqHost}/{$reqPath}";
      
      $source_ip = getenv('SOURCE_IP');   // test 클라이언트->서버 (사무실 외부 공인 ip)를 설정해야함.
      if (!$source_ip) {
        $source_ip = $request->getAttribute('ip_address');
      }
      
      $policy = array(
        'ExpireTimeSec' => $this->get('settings')['urlsign_info']['expireTime'],
        'SourceIp' => $source_ip,
        'IpSubnet' => '8',
        'Resource' => "/{$reqPath}"
      );
      
      $urlsign = new Urlsign(getenv('PRIVATE_KEY'));
      $down_url = $urlsign->getSignedUrl($reqUrl, $policy);
      if ($down_url === false) {
        return $response->withJson(['errorCode' => ERROR_DOWNLOAD_POLICY, 'errorMessage' => '다운로드 url 서명 실패'], 404);
      }
      
      $this->logger->info(
        '다운로드 URL 요청 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );
      
      return $response->withJson(['down_url' => $down_url], 200);
    }
  })->add(new Validation($validators['downurl']));

  /**
   * 추천자료(영화) 조회 서비스
   * 
   * @param int limit   (선택)
   * @param int offset  (선택)
   * 
   * @return 
   */
  $this->get('/contents/recommends/movie', function(Request $request, Response $response, array $args) {
    // initial log setting.
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    // check parameters.
    $params = array();
    ($request->getQueryParam('limit') !== null) ? ($params['limit'] = $request->getQueryParam('limit')) : ($params['limit'] = 20);
    ($request->getQueryParam('offset') !== null) ? ($params['offset'] = $request->getQueryParam('offset')) : ($params['offset'] = 0);

    // validate parameters.
    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      
      try {
        // 추천자료 조회
        $profile->start('RECOMMENDS');
        $contents = new Contents($this->sdb, $this->get('settings')['site']);

        $total = $contents->countContentRecommendsMovie();
        $recommendsMovie = $contents->getContentRecommendsMovie($params);

        $this->logger->info(
          '추천자료(영화) 조회 완료',
          array_merge($log_form, ['keyword'=> 'RECOMMENDS', 'duration' => $profile->end('RECOMMENDS')])
        );
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('추천자료(영화) 조회 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'RECOMMENDS', 'duration' => $profile->end('RECOMMENDS')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      // 추천자료 bbs_idx 리스트 추출
      $bbs_idx_list = '';
      foreach ($recommendsMovie as $v) {
        $bbs_idx_list .= $v['bbs_idx'] . ',';
      }
      $bbs_idx_list = rtrim($bbs_idx_list, ',');

      
      try {
        // 추천자료 추가정보 조회
        $profile->start('CONTENTS');

        $retContents = $contents->getContents(['id' => $bbs_idx_list, 'limit' => $params['limit']]);

        $this->logger->info(
          '추천자료(영화) 추가 정보 조회 완료',
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('CONTENTS')])
        );
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('추천자료(영화) 컨텐츠 조회 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('CONTENTS')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      // 추천자료(영화) + 추가정보 
      $result = array();
      $i = 0;

      foreach ($recommendsMovie as $v) {
        $contentInfo = array();

        // FIXME: 해당 부분 다른 방법으로 처리 할 수 있지 않을까??
        foreach ($retContents as $v2) {
          if ($v['bbs_idx'] === $v2['bbs_idx']) {
            $contentInfo = $v2;
            break;
          }
        }

        try {
          $v['point'] = $contents->getContentPrice($contentInfo);
          $v['total_size'] = $contents->convertSize($contentInfo['m_size']);
          if (!$v['total_size']) {
            !$v['total_size'] and $v['total_size'] = $contents->converSize($contentInfo['size'] * 0.36);
          }
          $v['title_img'] = $contents->getContentImage($contentInfo);
          $v['category'] = $contentInfo['code_cate2'];
          $retFiles = $contents->getFiles(array('bbs_idx' => $v['bbs_idx']));

          foreach ($retFiles as $v2) {
            $v['file_idx'][] = $v2['file_idx'];
          }
          $v['file_cnt'] = count($retFiles);
        }
        catch(\PDOException $e) {
          $this->logger->error(
            sprintf('추천자료 추가정보 조회 실패 메세지[%s]', $e->getMessage()),
            array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
          );

          return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
        }
        
        $result[$i++] = $v;
      }

      // 추천자료(영화) 정보 조회 성공 결과값 리턴
      $this->logger->info(
        '추천자료(영화) 정보 조회 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withJson(['count' => $total, 'recommendsMovie' => $result], 200);
    }
  })->add(new Validation($validators['recommends']));

  /**
   * 컨텐츠 회차정보 조회 서비스
   * 
   * @param int id    컨텐츠 번호(필수)
   * @param int limit 시리즈 페이지당 컨텐츠 조회 수 제한(선택) - default) 20
   * @param int page  시리즈 페이지 번호(선택)
   * @param int sort 컨텐츠 정렬 순서(선택) 0:DESC 1:ASC
   * @param int is_series 회차정보가 존재하는지 확인
   * 
   */
  $this->get('/contents/series/{id}', function(Request $request, Response $response, array $args) {
    // initial log setting.
    $profile = new Profile();
    $profile->start('ACTION');
    $log_form = $request->getAttribute('session')['log_form'];
    $tid = $log_form['transactionID'];
    $tdepth = $log_form['transactionDepth'];

    // check parameters.
    $params = array();
    $params['id'] = $args['id'];
    foreach (array('limit', 'page', 'sort', 'is_series') as $v) {
      if (($request->getQueryParam($v)) !== null) {
        $params[$v] = $request->getQueryParam($v);
      }
    }

    $this->logger->info(
      sprintf(
        '컨텐츠 시리즈 요청 id[%d] limit[%d] page[%d] sort[%d] is_series[%d]',
        $params['id'],
        $params['limit'],
        $params['page'],
        $params['sort'],
        $params['is_series']
      ),
      array_merge($log_form, ['keyword' => 'REQUEST'])
    ); 

    // validate parameters.
    if ($request->getAttribute('has_errors')) {
      $this->logger->error(
        'validation 에러 메세지',
        array_merge($log_form, ['keyword' => 'VALIDATION', 'errorsValidation' => $request->getAttribute('errors')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
    }
    else {
      // 1. 컨텐츠 정보 조회
      try {
        $profile->start('CONTENTS');
        $contents = new Contents($this->sdb, $this->get('settings')['site']);
        $retContents = $contents->getContents($params);
        $retContentsJehyu = $contents->getContentJehyu($params['id']);
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('컨텐츠 정보 조회 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withStatus(204);
      }

      $this->logger->info(
        '컨텐츠 정보 조회 완료',
        array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('CONTENTS')])
      );

      // 2. 제휴 컨텐츠만 시리즈 정보가 존재함
      if ($retContents[0]['chkcopy'] !== 'Y') {
        $this->logger->info(
          '시리즈 정보가 없는 컨텐츠',
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withStatus(204);
      }

      // 3. BD_MV, BD_AD 제외
      if (($retContents[0]['code_cate2'] === 'BD_MV') || ($retContents[0]['code_cate2'] === 'BD_AD')) {
        $this->logger->info(
          sprintf('해당 시리즈는 검색하지 않음 [%s]', $retContents[0]['code_cate2']),
          array_merge($log_form, ['keyword' => 'CONTENTS', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_IS_NOT_SERIES, 'errorMessage' => '회차별 자료가 없는 컨텐츠입니다.'], 200);
      }

      // 4. 회차 정보 조회
      try {
        $profile->start('EPISODE');

        $retEpisode = $contents->getEpisode($retContentsJehyu[0]['contents_id']);
        $totalEpisode = $contents->countTotalEpisode($retEpisode['cidx']);
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('에피소드 정보 조회 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'EPISODE', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        '에피소드 정보 조회 완료',
        array_merge($log_form, ['keyword' => 'EPISODE', 'duration' => $profile->end('EPISODE')])
      );

      /**
       * 4-1. 시리즈 정보 존재 확인
       *  - 시리즈 정보가 존재하는지 or 영화 추천자료인지 확인하기 위한 API로 사용 할 경우
       *   시리즈 정보만 확인하고 response를 return하도록 설정
       */
      if (isset($params['is_series']) && ($params['is_series'] == 1)) {
        $this->logger->info(
          '시리즈 정보 유무 확인 성공',
          array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );

        if (count($retEpisode) > 0) {
          return $response->withJson(['type' => 'series'], 200);
        }
        else {
          return $response->withJson(['type' => 'movie'], 200);
        }
      }

      /**
       * 5. 페이지 설정
       *  - 24시간 이내의 등록된 컨텐츠는 페이지 계산을 하지 않음.
       *  - limit 값은 파라미터로 받고, 없으면 default 20
       *  - page 값이 0으로 요청한 경우 사용하지 않음.
       *  - page 값이 total_page 보다 높은 경우 total_page 값으로 고정.
       */
      !isset($params['limit']) and $params['limit'] = 20;
      $total_page = ceil($totalEpisode / $params['limit']);

      if (
        (isset($params['page']) && ($params['page'] < 1)) &&
        (
          ($retContents[0]['regdate'] > (time() - 86400)) ||
          ($totalEpisode < $params['limit'])
        )
      ) {
        $page = 1;
      }
      else {
        if (isset($params['page']) && ($params['page'] != 0)) {
          // page 값이 존재하면 요청 page로 설정
          if ($params['page'] > $total_page) {
            $page = $total_page;
          }
          else {
            $page = $params['page'];
          }
        }
        else {
          // page 값이 존재하지 않으면 page 계산
          try {
            $numberEpisode = $contents->countEpisode($retEpisode['cidx'], $retEpisode['episode']);
          }
          catch (\PDOException $e) {
            $this->logger->error(
              sprintf('회차별 카운팅 실패 메세지[%s]', $e->getMessage()),
              array_merge($log_form, ['keyword' => 'PAGE', 'duration' => $profile->end('ACTION')])
            );
          }
          
          $page = 1;
          if (isset($params['sort']) && ($params['sort'] == 1)) {
            $page = ceil($numberEpisode / $params['limit']);
          }
          else {
            $nextEpisode = $totalEpisode - $numberEpisode;
            $page = ceil(($nextEpisode + 1) / $params['limit']);
          }
        }
      }

      // 6. 회차 리스트 정보 조회
      try {
        $profile->start('SERIES');
        $retSeriesList = $contents->getContentSeries(
          $retEpisode['cidx'],
          $page,
          $params['limit'],
          $params['sort'] ? 'asc' : 'desc'
        );
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('회차 리스트 조회 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'SERIES', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }
      
      $this->logger->info(
        '회차 리스트 조회 완료',
        array_merge($log_form, ['keyword' => 'SERIES', 'duration' => $profile->end('SERIES')])
      );

      // 7. 리스폰스 데이터 정규화
      $result = array();
      $i = 0;

      try {
        $profile->start('NORMALIZATION');

        foreach ($retSeriesList as $v) {
          $content = $contents->getContents(array('id' => $v['bbs_idx']))[0];

          $result[$i]['bbs_idx'] = $v['bbs_idx'];
          $result[$i]['title'] = $content['title'];
          $result[$i]['episode'] = intval($v['episode']);
          $result[$i]['date'] = substr($v["opendate"], 0, 4).substr($v["opendate"], 5, 2).substr($v["opendate"], 8, 2);
          $result[$i]['point'] = $contents->getContentPrice($content);
          $result[$i]['category'] = $content['code_cate2'];
          $result[$i]['title_img'] = $contents->getContentImage($content);

          $result[$i]['total_size'] = $contents->convertSize($content['m_size']);
          if (!$v['total_size']) {
            $ret[$i]['total_size'] = $contents->convertSize($v['size'] * 0.36);
          }

          $file_list = $contents->getFiles(array('bbs_idx' => $v['bbs_idx']));

          $result[$i]['file_cnt'] = count($file_list);

          foreach ($file_list as $v) {
            $result[$i]['file_idx'][] = $v['file_idx'];
          }

          $i++;
        }
      }
      catch (\PDOException $e) {
        $this->logger->error(
          sprintf('리스폰스 정규화 실패 메세지[%s]', $e->getMessage()),
          array_merge($log_form, ['keyword' => 'NORMALIZATION', 'duration' => $profile->end('NORMALIZATION')])
        );

        return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
      }

      $this->logger->info(
        '리스폰스 데이터 정규화 완료',
        array_merge($log_form, ['keyword' => 'NORMALIZATION', 'duration' => $profile->end('NORMALIZATION')])
      );

      $lastResult = array(
        'title' => $retEpisode['title'],
        // 'broadcaster' => $retEpisode['broadcaster'],
        // 'show_age' => $retEpisode['show_age'],
        // 'poster_mobile' => $retEpisode['poster_mobile'],
        'total_page' => $total_page,
        'page' => intval($page),
        'cidx' => intval($retEpisode['cidx']),
        'episode' => intval($retEpisode['episode']),
        'list' => $result,
      );

      $this->logger->info(
        '컨텐츠 회차별 자료 조회 성공',
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withJson($lastResult, 200);
    }
  })->add(new Validation($validators['series']));
});