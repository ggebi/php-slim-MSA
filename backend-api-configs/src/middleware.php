<?php
// Application middleware
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use Geggleto\Acl\AclRepository;

/**
 * app 실행 BEFORE와 AFTER 처리를 수행
 * BEFORE
 *  1. transaction id 생성
 *  2. transaction depth 생성
 *  3. Logging에 필요한 설정 값 설정
 * AFTER
 *  1. X-Transaction-Id 헤더 추가 (서버의 system log와 클라이언트의 log를 비교 할 용도)
 * 
 * @param Slim\Http\Request         $request
 * @param Slim\Http\Response        $response
 * @param callble                   $next
 * 
 * @return Response                 $response
 */
$app->add(function (Request $request, Response $response, callable $next) {
  //BEFORE
  $token = $request->getAttribute('token');
  $userid = $token['userid'];
  !strlen($userid) and $userid = 'guest-user';
  $tid = $request->getHeaderLine('X-Transaction-Id');
  !strlen($tid) and $tid = (hash('md5', $userid.time().rand(0, 9)));
  $tdepth = intval($request->getHeaderLine('X-Transaction-Depth')) + 1;
  $route = $request->getAttribute('route');
  
  $session = array(
    'log_form' => array(
      'ipAddress' => $request->getAttribute('ip_address'),
      'transactionID' => $tid,
      'transactionDepth' => $tdepth,
      'userId' => $userid,
      'uriPath' => $route->getPattern(),
    ),
  );
  $request = $request->withAttribute('session', $session);
  
  $response = $next($request, $response);
  
  //AFTER
  $response = $response->withHeader('X-Transaction-Id', $tid);
  
  return $response;
});

/**
 * akrabat/rka-ip-address-middleware
 * ip 주소 탐색 middleware
 */
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));