<?php

namespace Bankmedia\Models;

use Firebase\JWT\JWT;

class Tokens
{
    protected $_redis;

    public function __construct(\Predis\Client $redis)
    {
        $this->_redis = $redis;
    }
    
    //토큰 생성
    public function createAccessToken(string $userid, string $role) : string
    {
        $time = time();

        $token = array(
            'iss' => 'http://m.yesf.com',
            'jti' => hash('md5', $userid . $time . 'access'),
            'iat' => $time,
            'exp' => $time + EXPIRED_ACCESS_TOKEN_TIME,
            'userid' => $userid,
            'role' => $role,
            'type' => 'access',
        );

        $key = getenv('JWT_SECRET');
        return JWT::encode($token, $key);
    }

    public function createRefreshToken(string $userid) : string
    {
        $time = time();

        $token = array(
            'iss' => 'http://m.yesf.com',
            'jti' => hash('md5', $userid . $time . 'refresh'),
            'iat' => $time,
            'exp' => $time + EXPIRED_REFRESH_TOKEN_TIME,
            'userid' => $userid,
            'type' => 'refresh',
        );

        $this->__save($userid, $token['jti'], $token['exp']);

        $key = getenv('JWT_SECRET');
        return JWT::encode($token, $key);
    }

    //토큰 갱신
    public function refreshToken(array $token)
    {
        $old_jti = $token['jti'];
        $userid = $token['userid'];
        $exp = EXPIRED_REFRESH_TOKEN_TIME;

        $this->__delete($userid, $old_jti);
        
        $token['jti'] = hash('md5', $userid.time().$token['type']);
        $token['iat'] = time();
        $token['exp'] = $token['iat'] + $exp;

        $this->__save($userid, $token['jti'], $exp);

        $key = getenv('JWT_SECRET');
        return JWT::encode($token, $key);
    }

    public function validateToken(array $token)
    {
        $key = $token['userid'] . ':' . $token['jti'];
        return $this->_redis->exists($key);
    }

    private function __save($userid, $jti, $ttl = 3600)
    {
        $key = $userid . ':' . $jti;
        
        return $this->_redis->set($key, 1)
            and $this->_redis->expire($key, $ttl);
    }

    private function __retimer($key, $ttl = 3600)
    {
        if (!$this->_redis->exists($key))
            return false;

        return $this->_redis->expire($key, $ttl);
    }

    private function __delete($userid, $jti)
    {
        $this->_redis->del($userid . ':' . $jti);
    }
}