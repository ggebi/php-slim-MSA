<?php
// Application middleware
use Slim\Http\Request;
use Slim\Http\Response;

$app->add(function (Request $request, Response $response, callable $next) {
  //BEFORE
  $token = $request->getAttribute('token');
  $userid = $token['userid'];
  !strlen($userid) and $userid = 'guest-user';
  $tid = $request->getHeaderLine('X-Transaction-Id');
  !strlen($tid) and $tid = (hash('md5', $userid.time().rand(0, 99)));
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

$app->add(new Tuupola\Middleware\JwtAuthentication([
  'path' => ['/'],
  'ignore' => ['/v1/auth/login'],
  'secret' => getenv('JWT_SECRET'),
  'secure' => false,
]));
  
$checkProxyHeaders = true;
$trustedProxies = ['10.0.0.1', '10.0.0.2'];
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));