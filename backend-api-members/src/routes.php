<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator as v;
use DavidePastore\Slim\Validation\Validation;
use Bankmedia\Models\Members;
use Bankmedia\Common\Profile;

/**
 * @file routes.php
 * @brief members API
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @api GET /members                            회원 리스트 조회
 * @api GET /members/{userid}                   회원 조회
 * @api GET /members/{userid}/point             포인트 조회
 * @api PUT /members/{userid}/point             포인트 수정
 * @api GET /members/{userid}/verify            패스워드 검증
 * @api GET /members/{userid}/configs           설정 조회
 * @api PUT /members/{userid}/configs           설정 변경
 * @api GET /members/{userName}/blacklist       블랙리스트 조회
 */
$app->group('/v1', function() {

    //create the validators
    $userIdValidator = v::alnum()->noWhitespace()->length(4, 12);
    $userPwValidator = v::alnum('~!@#$%^&*()_+')->noWhitespace()->length(4, 12);
    $userPwAutoValidator = v::alnum()->noWhitespace()->length(16, 64);
    $limitValidator = v::optional(v::numeric()->between(10, 50));
    $offsetValidator = v::optional(v::numeric()->positive());
    $pointNameValidator = v::stringType()->noWhitespace()->length(4, 6);
    $pointValidator = v::numeric();
    $configPushValidator = v::numeric();
    $configPrivateValidator = v::numeric();
    
    $validators = array(
        'members' => array(
            'userid' => $userIdValidator,
            'limit' => $limitValidator,
            'offset' => $offsetValidator,
        ),
        'members-verify' => array(
            'userid' => $userIdValidator,
            'userpw' => v::optional($userPwValidator),
            'userpw_auto' => v::optional($userPwAutoValidator),
        ),
        'member-point' => array(
            'userid' => $userIdValidator,
            'point_name' => $pointNameValidator,
            'point' => $pointValidator,
        ),
        'config-push' => array(
            'userid' => $userIdValidator,
            'push_on' => $configPushValidator,
            'agree_private' => v::optional($configPrivateValidator),
        ),
    );

    /**
     * 전체 회원정보 조회
     * 
     * @return array
     */
    $this->get('/members', function(Request $request, Response $response, array $args) {
        if ($request->getAttribute('has_errors')) { //validation 실패
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $userid = $request->getQueryParam('userid');
            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            if ( 
                (isset($userid) && ($userid == $token['userid'])) ||
                (isset($token['role']) && ($token['role'] == 'admin'))
            ) {
                try {
                    $members = new Members($this->sdb);

                    $retMembers = $members->getMembers([
                        'userid' => $userid,
                    ]);
                    if (count($retMembers) < 1) {
                        $this->logger->warning(
                            sprintf(
                                '회원정보 없음:%s',
                                $userid
                            ),
                            array_merge(
                                $log_form,
                                [
                                    'keyword' => 'MEMBER',
                                    'duration' => $profile->end('ACTION'),
                                ]
                            )
                        );

                        return $response->withJson(['errorCode' => ERROR_MEMBER_NOT_FOUND, 'errorMessage' => '회원정보 조회 실패'], 404);
                    }

                    $this->logger->info(
                        sprintf(
                            '회원정보 요청 성공:%s',
                            $userid
                        ),
                        array_merge(
                            $log_form,
                            [
                                'keyword' => 'ACTION',
                                'duration' => $profile->end('ACTION'),
                            ]
                        )
                    );

                    return $response->withJson(['members' => $retMembers], 200);
                }
                catch(\PDOException $e) {
                    $this->logger->error(
                        sprintf(
                            '회원정보 요청 실패:%s 에러:%s',
                            $userid,
                            $e->getMessage()
                        ),
                        array_merge(
                            $log_form,
                            [
                                'keyword' => 'MEMBER',
                                'duration' => $profile->end('ACTION'),
                            ]
                        )
                    );

                    return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => '서버 에러'], 500);
                }
            }
            else {
                $this->logger->warning(
                    sprintf(
                        '회원정보 요청 권한 없음:%s',
                        $userid
                    ),
                    array_merge(
                        $log_form,
                        [
                            'keyword' => 'MEMBER',
                            'duration' => $profile->end('ACTION'),
                        ]
                    )
                );

                return $response->withJson(array('errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'), 403);
            }
        }
    })->add(new Validation($validators['members']));

    /**
     * 회원정보 조회
     * 
     * @param string userid
     * @return array    (회원 정보))
     *   {
     *     "address": "주소",
     *     "adult": "0",
     *     "auth":  "0",
     *     "bandate": "0",
     *     "chk_iphone": "1",
     *     ...
     *   }
     */
    $this->get('/members/{userid}', function(Request $request, Response $response, array $args) {

        if ($request->getAttribute('has_errors')) { //validation 실패
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $userid = $args['userid'];
            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            // admin이 아니고, 일반 유저가 다른 유저의 정보를 조회하려고 할 때 예외 처리 
            if ( 
                (isset($userid) && ($userid == $token['userid'])) ||
                (isset($token['role']) && (($token['role'] == 'admin') || $token['role'] == 'system'))
            ){
                try {
                    $members = new Members($this->sdb);

                    $member = $members->getMember($userid);
                    if ($member === false) { //회원정보 정보 없음
                        $this->logger->warning(
                            sprintf('회원 정보 없음:%s', $userid),
                            array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
                        );

                        return $response->withJson(['errorCode' => ERROR_MEMBER_NOT_FOUND, 'errorMessage' => '회원 정보 없음'], 404);
                    }
                }
                catch (\PDOException $e) {
                    $this->logger->error(
                        sprintf('회원정보 조회 실패:%s 메세지:%s', $userid, $e->getMessage()),
                        array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 에러'], 500);
                }

                $this->logger->info(
                    sprintf('회원정보 요청 성공:%s', $userid),
                    array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                );

                return $response->withJson($member, 200);
            }
            else {
                $this->logger->warning(
                    sprintf('회원정보 요청 권한 없음:%s', $userid),
                    array_merge($log_form, ['keyword' => 'MEMBER', 'duration' => $profile->end('ACTION')])
                );

                return $response->withJson(['errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'], 403);
            }
        }
    })->add(new Validation($validators['members']));

    /**
     * 유저의 포인트 정보 조회
     * 
     * @param string userid
     * @return array    (회원 포인트 정보))
     *   {
     *     "auto": "N",
     *     "auto_cancle": "N",
     *     "coupon":  "0",
     *     "disk_space": "0",
     *     "fix_end": "0",
     *     ...
     *   }
     */
    $this->get('/members/{userid}/point', function(Request $request, Response $response, array $args) {
        if ($request->getAttribute('has_errors')) { //validation 실패
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            // admin, system 전부 조회가능, user는 자신의 정보만 조회 가능
            if (
                (isset($token['role']) && ($token['role'] == 'admin' || $token['role'] == 'system')) ||
                (isset($token['userid']) && ($token['userid'] == $args['userid']))
            ) {
                $members = new Members($this->sdb);

                $point = $members->getPoint($args['userid']);
                if (is_array($point) === false) {
                    $this->logger->warning(
                        sprintf(
                            '회원 포인트 정보 없음:%s',
                            $args['userid']
                        ),
                        array_merge(
                            $log_form,
                            [
                                'keyword' => 'ACTION',
                                'duration' => $profile->end('ACTION'),
                            ]
                        )
                    );

                    return $response->withJson(
                        [
                            'errorCode' => ERROR_MEMBER_NOT_FOUND,
                            'errorMessage' => '회원 정보 없음',
                        ],
                        404
                    );
                }
    
                $this->logger->info(
                    sprintf(
                        '회원 포인트 정보 조회 성공:%s',
                        $args['userid']
                    ),
                    array_merge(
                        $log_form,
                        [
                            'keyword' => 'ACTION',
                            'duration' => $profile->end('ACTION'),
                        ]
                    )
                );

                return $response->withJson($point, 200);
            }
            else {
                $this->logger->warning(
                    sprintf(
                        '회원 포인트 정보 조회 권한 없음:%s',
                        $args['userid']
                    ),
                    array_merge(
                        $log_form,
                        [
                            'keyword' => 'ACTION',
                            'duration' => $profile->end('ACTION'),
                        ]
                    )
                );
    
                return $response->withJson(['errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'], 403);
            }
        }
    })->add(new Validation($validators['members']));

    /**
     * 포인트 정보 변경
     * 
     * @param string userid         변경 유저 아이디
     * @param string point_name     변경 point 종류
     * @param string point          변경 포인트 값
     * @return status code
     */
    $this->put('/members/{userid}/point', function(Request $request, Response $response, array $args)
    {
        if ($request->getAttribute('has_errors')) { //validation 실패
            $data = array('errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors'), 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];
            $params = $request->getParsedBody();

            // 포인트를 수정하는 API는 관리자의 인가를 필요함으로써 보안 향상 (특정 API에서만 접근 가능하도록)
            if (
                (isset($token['type']) && ($token['type'] == 'access')) &&
                (isset($token['role']) && ($token['role'] == 'admin' || $token['role'] == 'system'))
            ) {
                try {
                    $members = new Members($this->mdb);
                    $ret = $members->setPoint($args['userid'], $params['point_name'], $params['point']);

                    if ($ret === false) { // 잘못 된 요청으로인한 쿼리오류 또는 수정된 결과가 없을 경우
                        $this->logger->info(
                            sprintf('%s 수정 실패 잘못 된 요청:%s', $params['point_name'], $args['userid']),
                            array_merge($log_form, ['keyword' => 'MEMBERS', 'duration' => $profile->end('ACTION')])
                        );

                        return $response->withJson(array('errorCode' => ERROR_SET_POINT, 'errorMessage' => '포인트 수정 실패'), 404);
                    }
                }
                catch (\PDOException $e) {
                    $this->logger->error(
                        sprintf('%s 수정 실패:%s 메세지:%s', $params['point_name'], $args['userid'], $e->getMessage()),
                        array_merge($log_form, ['keyword' => 'MEMBERS', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 에러'], 500);
                }

                $this->logger->info(
                    sprintf('%s 수정 성공:%s', $params['point_name'], $args['userid']),
                    array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                );

                return $response->withStatus(200);
            }
            else {
                return $response->withJson(array('errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'), 403);
            }
        }
    })->add(new Validation($validators['member-point']));

    /**
     * 유저의 패스워드 정보 체크
     * 
     * @param string userid     아이디
     * @param string userpw     패스워드 (body에 입력)
     * @return array
     *              {
     *                  "level": "9"
     *              }
     * 
     * TODO: 임시적으로 자동로그인을 처리
     */
    $this->post('/members/verify', function(Request $request, Response $response, array $args)
    {
        if ($request->getAttribute('has_errors')) {
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            if (
                (isset($token['type']) && ($token['type'] == 'access')) &&
                (isset($token['role']) && ($token['role'] == 'admin' || $token['role'] == 'system'))
            ) {
                $userid = $request->getParsedBodyParam('userid');
                $userpw = $request->getParsedBodyParam('userpw');
                $userpw_auto = $request->getParsedBodyParam('userpw_auto');

                try {
                    $members = new Members($this->sdb);
                    $ret_verify = '';

                    if ($userpw_auto === null) {
                        $ret_verify = $members->verifyPassword($userid, $userpw);
                    }
                    else {
                        $ret_verify = $members->verifyPasswordAuto($userid, $userpw_auto);
                    }

                    if ($ret_verify === true) {
                        $member = $members->getMember($userid);

                        $this->logger->info(
                            sprintf('패스워드 검증 성공:%s', $userid),
                            array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                        );

                        return $response->withJson(['level' => $member['level']], 200);
                    }
                    else {
                        $this->logger->warning(
                            sprintf('패스워드 검증 실패:%s', $userid),
                            array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                        );

                        return $response->withJson(array('errorCode' => ERROR_VERIFY_PASSWORD, 'errorMessage' => '패스워드 검증 실패'), 401);
                    }
                }
                catch(\PDOException $e) {
                    $this->logger->error(
                        sprintf('패스워드 검증 실패:%s 메세지:%s', $userid, $e->getMessage()),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => $e->getMessage()], 500);
                }
            }
            else {
                return $response->withJson(array('errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'), 403);
            }
        }
    })->add(new Validation($validators['members-verify']));

    /**
     * 현재 알림 설정 값 조회
     * @param string userid     아이디
     * @return array (설정정보)
     */
    $this->get('/members/{userid}/configs', function(Request $request, Response $response, array $args)
    {
        if ($request->getAttribute('has_errors')) {
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            if (
                ($token['userid'] == $args['userid']) ||
                (isset($token['role']) && ($token['role'] == 'admin'))
            ) {
                try{
                    $members = new Members($this->sdb);
                    $config = $members->getConfigNotice($token['userid']);              
                    if (!is_array($config)) {
                        return $response->withStatus(204);
                    }

                    $this->logger->info(
                        sprintf('알림 설정 조회 성공:%s', $args['userid']),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withJson(['push_on' => $config['agree_event']], 200);
                }
                catch (\PDOException $e) {
                    $this->logger->error(
                        sprintf('알림 설정 조회 실패:%s 메세지:%s', $args['userid'], $e->getMessage()),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withJson(['errorCode' => ERROR_SERVER_UNKNOWN, 'errorMessage' => 'DB 서버 에러'], 500);
                }
            }
            else {
                $this->logger->warning(
                    sprintf(
                        '알림 설정 조회 권한 없음:%s',
                        $args['userid']
                    ),
                    array_merge(
                        $log_form,
                        [
                            'keyword' => 'ACTION',
                            'duration' => $profile->end('ACTION'),
                        ]
                    )
                );

                return $response->withJson(array('errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'), 403);
            }
        }
    })->add(new Validation($validators['members']));

    /**
     * 알림 설정 값 수정 또는 추가
     * 
     * @param string    userid          아이디
     * @param int       push_on         알림 설정
     * @param int       agree_private   예약
     * 
     * @return  http status code
     */
    $this->put('/members/{userid}/configs', function(Request $request, Response $response, array $args)
    {
        if ($request->getAttribute('has_errors')) {
            $data = array(
                'errorCode' => ERROR_INVALID_PARAM,
                'errorMessage' => $request->getAttribute('errors'),
            );

            return $response->withJson($data, 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];
            $options = array_merge(array('userid' => $args['userid']), $request->getParsedBody());

            if (
                ($token['userid'] == $args['userid']) ||
                (isset($token['role']) && ($token['role'] == 'admin'))
            ) {
                try {
                    $members = new Members($this->mdb);
                    $ret = $members->setConfigNotice($options);
                
                    $this->logger->info(
                        sprintf('알림 설정 수정 성공:%s', $args['userid']),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );

                    return $response->withStatus(200);
                }
                catch (\Exception $e) {
                    $this->logger->error(
                        sprintf('알림 설정 조회 실패:%s 메세지:%s', $args['userid'], $e->getMessage()),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );
                    
                    return $response->withJson(['errorCode' => $e->getCode(), 'errorMessage' => 'DB 서버 에러'], 500);
                }
            }
            else {
                $this->logger->warning(
                    sprintf(
                        '알림 설정 수정 권한 없음:%s',
                        $args['userid']
                    ),
                    array_merge(
                        $log_form,
                        [
                            'keyword' => 'ACTION',
                            'duration' => $profile->end('ACTION'),
                        ]
                    )
                );

                return $response->withJson(['errorCode' => ERROR_UNAUTHORIZED, 'errorMessage' => '권한 없음'], 403);
            }
        }
    })->add(new Validation($validators['config-push']));

    /**
     * 블랙리스트 유저 확인
     * 판매자가 특정 유저의 차단상태를 확인하는 API입니다.
     * @param string    seller
     * @param string    nickname
     */
    $this->get('/members/{userName}/blacklist', function(Request $request, Response $response, array $args) {
        if ($request->getAttribute('has_errors')) {
            return $response->withJson(['errorCode' => ERROR_INVALID_PARAM, 'errorMessage' => $request->getAttribute('errors')], 412);
        }
        else {
            $profile = new Profile();
            $profile->start('ACTION');

            $seller_nickname = $args['userName'];
            $target_nickname = $request->getQueryParam('target');
            $token = $request->getAttribute('token');
            $log_form = $request->getAttribute('session')['log_form'];
            $tid = $log_form['transactionID'];
            $tdepth = $log_form['transactionDepth'];

            try {
                $members = new Members($this->sdb);

                $ret = $members->chkBlackList($seller_nickname, $target_nickname);
                if ($ret !== false) {
                    $this->logger->info(
                        sprintf('블랙리스트 조회 차단 판매자:%s 타켓:%s', $seller_nickname, $target_nickname),
                        array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                    );

                    // 블랙리스트 등록되어있는 상태
                    return $response->withJson(['blacklist' => $ret], 200);
                }

                $this->logger->info(
                    sprintf('블랙리스트 조회 성공 판매자:%s 타겟:%s', $seller_nickname, $target_nickname),
                    array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                );
                // 블랙리스트 등록되어있지 않은 상태
                return $response->withStatus(200);
            }
            catch(\PDOException $e) {
                $this->logger->error(
                    sprintf('블랙리스트 차단 조회 실패:%s', $seller_nickname),
                    array_merge($log_form, ['keyword' => 'ACTION', 'duration' => $profile->end('ACTION')])
                );

                return $response->withJson(['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()], 500);
            }
        }
    });

    /**
     * @brief 회원가입(유저 추가)
     * @todo 미완성
     */
    // $this->post('/members', function(Request $request, Response $response, array $args) {
    //     if ($request->getAttribute('has_errors')) {
    //         $data = array(
    //             'errorCode' => ERROR_INVALID_PARAM,
    //             'errorMessage' => $request->getAttribute('errors'),
    //         );

    //         return $response->withJson($data, 412);
    //     }
    //     else {
    //         $body = $request->getParsedBody();

    //         $member = new Members($this->mdb);
    //         $idx = $member->createMember($body);
    
    //         return false !== $idx ? $response->withJson(array('member' => array_merge($body, array('idx' => $idx))), 201) : $response->withJson(array('errorCode' => '????', 'errorMessage' => 'failed to create member'), 500);
    //     }
    // })->add(new Validation($validators['members']));

});
