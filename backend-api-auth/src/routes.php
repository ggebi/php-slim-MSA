<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use GuzzleHttp\Client;
use Bankmedia\Models\Tokens;
use Bankmedia\Common\Profile;

/**
 * @file routes.php
 * @brief auth API
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @method POST /auth/login
 * @method POST /auth/refresh
 */
$app->group('/v1', function() {
    
    /**
     * @brief 로그인 
     * @param string userid
     * @param string userpw
     * @param string userpw_auto
     * @return array
     *  {
     *      "access_token": "access_token",
     *      "refresh_token": "refresh_token",
     *  }
     * @todo role: admin, system, user, guest(토큰 없는 접근)
     */
    $this->post('/auth/login', function(Request $request, Response $response, array $args) {
        $profile = new Profile();
        $profile->start('ACTION');

        $log_form = $request->getAttribute('session')['log_form'];
        $tid = $log_form['transactionID'];
        $tdepth = $log_form['transactionDepth'];
        $userid = $request->getParsedBodyParam('userid');
        $userpw = $request->getParsedBodyParam('userpw');
        $userpw_auto = $request->getParsedBodyParam('userpw_auto');

        $tokens = new Tokens($this->redis);

        // members 호출에 필요한 token 생성
        $jwt = $tokens->createAccessToken(
            'auth-api', 
            'system'
        );

        try {
            $client = new Client([
                'base_uri' => 'http://api.backend-api-members/v1/',
                'timeout'  => 2.0,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $jwt,
                    'X-Forwarded-For' => $request->getAttribute('ip_address'),
                    'X-Transaction-Id' => $tid,
                    'X-Transaction-Depth' => $tdepth,
                    'User-Agent' => GuzzleHttp\default_user_agent() . ' Auth/1.0',
                )
            ]);

            // 패스워드 검증 요청
            $resp = $client->request('POST', "members/verify", [
                'json' => [
                    'userid' => $userid,
                    'userpw' => $userpw,
                    'userpw_auto' => $userpw_auto,
                ]
            ]);

            $member = json_decode($resp->getBody(), true);
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->warning(
                sprintf('로그인 실패:%s 메세지:%s', $userid, $e->getMessage()),
                array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(array('errorCode' => ERROR_LOGIN_FAILED, 'errorMessage' => '로그인 정보가 일치하지 않습니다. 확인 후 다시 로그인해 주세요.'), 401);
        }
        catch (Exception $e) {
            $this->logger->error(
                sprintf('로그인 실패:%s 메세지:%s', $userid, $e->getMessage()),
                array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
        }

        $role = '';
        if ($member['level'] == 1) {
            $role = 'admin';
        }
        else {
            $role = 'user';
        }

        try {
            // access_token, refresh_token 생성
            $access_token = $tokens->createAccessToken(
                $userid,
                $role
            );
            $refresh_token = $tokens->createRefreshToken(
                $userid
            );

            $this->logger->info(
                sprintf('로그인 성공:%s', $userid),
                array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(
                [
                    'access_token' => $access_token,
                    'refresh_token' => $refresh_token,
                ], 
                200
            );
        }
        catch (Exception $e) {
            $this->logger->error(
                sprintf('로그인 토큰 생성 실패:%s 메세지:%s', $userid, $e->getMessage()),
                array_merge($log_form, ['keyword' => 'TOKEN', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
        }
    });

    /**
     * access token 재발급
     * @return string access_token
     */
    $this->post ('/auth/refresh', function(Request $request, Response $response, array $args) {
        $profile = new Profile();
        $profile->start('ACTION');

        $log_form = $request->getAttribute('session')['log_form'];
        $tid = $log_form['transactionID'];
        $tdepth = $log_form['transactionDepth'];
        $token = $request->getAttribute('token');
        $userid = $token['userid'];

        $this->logger->debug(
            sprintf('refresh요청 key[%s] type[%s]', $token['userid'].':'.$token['jti'], $token['type']),
            array_merge($log_form, ['keyword' => 'REQUEST'])
        );

        try {
            $profile->start('TOKEN_LOAD');
            
            $tokens = new Tokens($this->redis);
            if (!$tokens->validateToken($token)) {
                $this->logger->info(
                    sprintf('REFRESH 토큰 만료:%s', $token['userid']),
                    array_merge($log_form, ['keyword' => 'TOKEN_LOAD', 'duration' => $profile->end('ACTION')])
                );

                return $response->withJson(['errorCode' => ERROR_EXPIRED_TOKEN, 'errorMessage' => 'REFRESH 토큰 만료'], 401);
            }
        }
        catch (Predis\Connection\ConnectionException $e) {
            $this->logger->error(
                sprintf('토큰 서버 에러:%s 메세지:%s', $token['userid'], $e->getMessage()),
                array_merge($log_form, ['keyword' => 'TOKEN_LOAD', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_VALIDATE_TOKEN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
        }

        // members 호출에 필요한 token 생성
        $jwt = $tokens->createAccessToken(
            'auth-api', 
            'system'
        );

        try {
            $client = new Client([
                'base_uri' => 'http://api.backend-api-members/v1/',
                'timeout'  => 2.0,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $jwt,
                    'X-Forwarded-For' => $request->getattribute('ip_address'),
                    'X-Transaction-Id' => $tid,
                    'X-Transaction-Depth' => $tdepth,
                    'User-Agent' => GuzzleHttp\default_user_agent() . ' Auth/1.0',
                )
            ]);

            $resp = $client->request('GET', "members/{$token['userid']}");
            $member = json_decode($resp->getBody(), true);
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $this->logger->warning(
                sprintf('토큰 갱신 회원정보 요청 실패:%s 메세지:%s', $token['userid'], $e->getMessage()),
                array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_MEMBER_NOT_FOUND, 'errorMessage' => '회원정보 요청을 실패했습니다.'], 404);
        }
        catch (Exception $e) {
            $this->logger->error(
                sprintf('회원정보 요청 실패:%s 메세지:%s', $userid, $e->getMessage()),
                array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'], 500);
        }

        // TODO: role 설정
        $role = '';
        if ($member['level'] === 1) {
            $role = 'admin';
        }
        else {
            $role = 'user';
        }

        try {
            // access_token, refresh_token 재발급
            $access_token = $tokens->createAccessToken(
                $userid,
                $role
            );
            $refresh_token = $tokens->refreshToken(
                $token
            );
        }
        catch (Exception $e) {
            $this->logger->error(
                sprintf('토큰 갱신 실패:%s 메세지:%s', $userid, $e->getMessage()),
                array_merge($log_form, ['keyword' => 'TOKEN', 'duration' => $profile->end('ACTION')])
            );

            return $response->withJson(array('errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '일시적인 오류가 발생하였습니다. 다시 시도해 주세요.'), 500);
        }

        $this->logger->info(
            sprintf('토큰 갱신 성공:%s', $token['userid']),    
            array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );

        return $response->withJson([
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ], 
        200);
    });

    /**
     * 토큰 값이 유효한지 확인
     */
    $this->get('/auth/me', function(Request $request, Response $response, array $args) {
        $profile = new Profile();
        $profile->start('ACTION');
        $log_form = $request->getAttribute('session')['log_form'];
        $tid = $log_form['transactionID'];
        $tdepth = $log_form['transactionDepth'];
        $is_valid = 0;

        $token = $request->getAttribute('token');
        if (intval($token['exp']) - intval($token['iat']) > 0) {
            $is_valid = 1;
        }

        $result = [
            'userid' => $token['userid'],
            'role' => $token['role'],
            'type' => $token['type'],
            'is_valid' => $is_valid,
        ];

        $this->logger->info(
            sprintf('토큰 유효성 체크: [%s]', $token['userid']),
            array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
        );
        
        return $response->withJson($result, 200);
    });
});
