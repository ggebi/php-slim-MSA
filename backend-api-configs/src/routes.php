<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use Bankmedia\Common\Profile;

/**
* @file routes.php
* @brief configs API
* @author 주형우 (jhwmon@bankmedia.co.kr)
* 
* @api GET /configs/versions          버정정보 조회
*/
$app->group('/v1', function() {
  $this->get('/configs/versions', function(Request $request, Response $response, array $args) {
    $profile = new Profile();
    $profile->start('ACTION');

    $log_form = $request->getAttribute('session')['log_form'];
    $app = $request->getQueryParam('app');

    if ($app === 'android') {
      $versions = $this->get('settings')['version']['android'];
    } else if ($app === 'ios') {
      $versions = $this->get('settings')['version']['ios'];
    } else {
      $this->logger->warning(
        sprintf('버전정보 조회 실패 app=%s', $app),
        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
      );

      return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => '알 수 없는 파라미터 값 입니다.'], 406);
    }

    $this->logger->info(
      sprintf('버전정보 조회 성공 app=%s', $app),
      array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
    );

    return $response->withJson($versions, 200);
  });
});