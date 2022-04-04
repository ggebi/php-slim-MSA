<?php

namespace Bankmedia\Models;

use KISA\KISA_SEED_SHA256;

/**
* @file Members.php
* @brief 회원 정보를 조회하는 클래스
* @author 주형우 (jhwmon@bankmedia.co.kr)
* 
* @method getMembers(array $options): array
* @method getMember(string $userId)
* @method getPoint(string $userId)
* @method setPoint(string $userId, string $pointName, int $point)
* @method verifyPassword($userid, $userpw): bool
* @method getPassword(string $str)
* @method getConfigNotice(string $userid)
* @method setConfigNotice(array $options)
* @method chkBlackList(string $seller_nickname, string $target_nickname)
* @method __createOldPassword($str)
* @method __createPassword($str)
*/ 
class Members {
  protected $db;
  private $default = [
    'limit' => 10,
    'offset' => 0,
  ];
  
  private $tableMember = '_member';
  private $tablePoint = 'mmsv_webhard_pay';
  private $tableConfigPush = 'app_push_down_agree';
  private $tableBlackList = '_friend_black';
  
  public function __construct($db) {
    $this->db = $db;
  }
  
  /**
  * 회원정보 리스트 조회
  * @param string userid
  * @param int limit
  * @param int offset
  * 
  * @return array
  */
  public function getMembers(array $options): array
  {
    $query = <<<SQL
      SELECT
        `idx`,
        `userid`,
        `level`,
        `nickname`,
        `auth`,
        `adult`
      FROM
        `{$this->tableMember}`
      WHERE
        1
SQL;
    
    $options = array_merge($this->default, $options);
    
    if (isset($options['userid'])) $query .= " AND userid LIKE :userid";
    
    $query .= " LIMIT :limit OFFSET :offset";
    
    try {
      $stmt = $this->db->prepare($query);
      
      if (isset($options['userid'])) $stmt->bindValue(':userid', $options['userid'].'%');
      
      $stmt->bindParam(':limit', $options['limit'], \PDO::PARAM_INT);
      $stmt->bindParam(':offset', $options['offset'], \PDO::PARAM_INT);
      $stmt->execute();
      
      return $stmt->fetchAll();
    }
    catch(\PDOException $e)
    {
      throw $e;
    }
  }
  
  /**
  * 유저정보 조회
  * @param string userid
  * @return array
  */
  public function getMember(string $userId) : array
  {
//     $query = <<<SQL
//       SELECT
//         idx,
//         userid,
//         userpw,
//         name,
//         level,
//         nickname,
//         jumin,
//         email,
//         tel,
//         hpcorp,
//         hp,
//         zipcode,
//         address,
//         recomid,
//         mailing,
//         auth,
//         adult,
//         ip,
//         ip_last,
//         connect,
//         last_connect,
//         note,
//         cp,
//         bandate,
//         control,
//         pg_id,
//         flag_admin,
//         regdate,
//         join_site,
//         chk_iphone,
//         member_key,
//         notice_date
//       FROM
//         {$this->tableMember}
//       WHERE
//         userid = :userid
// SQL;
    $query = <<<SQL
      SELECT
        `idx`,
        `userid`,
        `level`,
        `nickname`,
        `auth`,
        `adult`
      FROM
        `{$this->tableMember}`
      WHERE
        `userid` = :userid
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userId, \PDO::PARAM_STR);
      $stmt->execute();
      
      return $stmt->fetch();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 포인트 정보 조회
  * @param string userId
  * @return array
  */
  public function getPoint(string $userId) : array
  {
//     $query = <<<SQL
//       SELECT
//         userid,
//         packet,
//         reward,
//         item,
//         point,
//         keeping,
//         recom,
//         mileage,
//         coupon,
//         thiat,
//         reward_bonus,
//         use_halfmonth,
//         pre_halfmonth,
//         disk_space,
//         fix_start,
//         fix_end,
//         fix_sub_end,
//         fix_mode,
//         fix_time,
//         time_start,
//         time_end,
//         auto,
//         auto_cancel
//       FROM
//         {$this->tablePoint}
//       WHERE
//         userid = :userid
// SQL;
    $query = <<<SQL
      SELECT
        `userid`,
        `packet`,
        `reward`,
        `item`,
        `point`,
        `recom`,
        `mileage`,
        `coupon`,
        `reward_bonus`
      FROM
        `{$this->tablePoint}`
      WHERE
        `userid` = :userid
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userId, \PDO::PARAM_STR);
      $stmt->execute();
      
      return $stmt->fetch();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 포인트 수정
  * @param string    userId
  * @param string    pointName
  * @param int       point
  * @return bool
  */
  public function setPoint(string $userid, string $pointName, int $point) : bool
  {
    $query = '';
    
    if( isset($pointName) && isset($point) && $point >= 0 ) {
      $query = "UPDATE {$this->tablePoint}";
      
      if($pointName == 'packet') {
        $query .= " SET packet = :setPoint";
      }
      elseif($pointName == 'point') {
        $query .= " SET point = :setPoint";
      }
      elseif($pointName == 'reward') {
        $query .= " SET reward = :setPoint";
      }
      elseif($pointName == 'item') {
        $query .= " SET item = :setPoint";
      }
      else { //안전코드
        $query .= " SET point = point";
      }
      
      $query .= " WHERE userid = :userid";
      $query .= " LIMIT 1";
    }
    
    try {
      $point = mb_convert_encoding($point, "EUC-KR", "UTF-8");
      $userid = mb_convert_encoding($userid, "EUC-KR", "UTF-8");
      
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':setPoint', $point, \PDO::PARAM_STR);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->execute();
      
      return 0 < $stmt->rowCount() ? true : false;
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 패스워드 체크
  * @param string userid
  * @param string userpw
  * @return bool
  */
  public function verifyPassword(string $userid, string $userpw) : bool
  {
    $query = <<<SQL
      SELECT
        COUNT(*) AS count
      FROM
        `{$this->tableMember}`
      WHERE
        `userid` = :userid
        AND (`userpw` = :userpw OR `userpw` = :old_userpw)
        AND control != 9
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->bindParam(':userpw', $this->__createPassword($userpw));
      $stmt->bindParam(':old_userpw', $this->__createOldPassword($userpw));
      $stmt->execute();
      
      $count = $stmt->fetch()['count'];
      return 0 < $count ? true : false;
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 자동 로그인 패스워드 체크
  * @param string userid
  * @param string userpw_auto
  * @return bool
  * @todo 임시로 만든 메소드, 향후에 다른 방법으로 자동로그인 구현 예정
  */
  public function verifyPasswordAuto(string $userid, string $userpw_auto): bool
  {
    $query = <<<SQL
      SELECT
        COUNT(*) AS count
      FROM
        `{$this->tableMember}`
      WHERE
        `userid` = :userid
        AND `userpw` = :userpw
        AND control != 9
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->bindParam(':userpw', $userpw_auto, \PDO::PARAM_STR);
      $stmt->execute();
      
      $count = $stmt->fetch()['count'];
      return 0 < $count ? true : false;
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 알림 설정 확인
  * @param string userid
  * @return array
  */
  public function getConfigNotice(string $userid)
  {
    $query = <<<SQL
      SELECT
        `userid`,
        `agree_event`
      FROM
        `{$this->tableConfigPush}`
      WHERE
        `userid` = :userid
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->execute();
      
      return $stmt->fetch();
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }
  
  /**
  * 알림 설정 변경
  * @param string userid
  * @return array
  */
  public function setConfigNotice(array $options) : bool
  {
    $time = time();
    $addQuery = '';
    isset($options['agree_private']) and $addQuery = ", agree_private = :agree_private, date_agree_private = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s')";
    
    $query = <<<SQL
      INSERT INTO
        `{$this->tableConfigPush}`
      SET
        `userid` = :userid,
        `agree_event` = :agree_event,
        `date_agree_event` = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s'),
        `date_live` = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s'),
        `regdate` = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s')
        {$addQuery}
      ON DUPLICATE KEY UPDATE
        `userid` = :userid,
        `agree_event` = :agree_event,
        `date_agree_event` = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s'),
        `date_live` = FROM_UNIXTIME({$time}, '%Y-%m-%d %H:%i:%s')
        {$addQuery}
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':agree_event', $options['push_on'], \PDO::PARAM_INT);
      isset($options['agree_private']) and $stmt->bindParam(':agree_private', $options['agree_private'], \PDO::PARAM_STR);
      
      $stmt->execute();
      
      $ret = $stmt->rowCount();
      return 0 < $ret ? true : false;
    }
    catch (\PDOException $e) {
      throw new \Exception($e->getMessage(), 999);
    }
  }
  
  /**
  * 판매자 차단 확인
  * @param string    seller_id
  * @param string    target_nickname
  * @return array    차단 정보
  */
  public function chkBlackList(string $seller_nickname, string $target_nickname)
  {
    $query = <<<SQL
      SELECT 
        *
      FROM
        {$this->tableBlackList}
      WHERE
        name = :name
        AND target_name = :target_name
        AND state = 'Y';
SQL;
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':name', $seller_nickname, \PDO::PARAM_STR);
      $stmt->bindParam(':target_name', $target_nickname, \PDO::PARAM_STR);
      $stmt->execute();
      
      return $stmt->fetch();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }
  
  public function getPassword(string $str)
  {
    return array($this->__createOldPassword($str), $this->__createPassword($str));
  }
  
  /**
  * @brief 예전 패스워드 생성
  * @param string userpw
  * @return string 암호화된 패스워드
  */
  private function __createOldPassword(string $str) : string
  {
    try {
      $query = "SELECT PASSWORD(:str) AS old_pwd";
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':str', $str);
      $stmt->execute();
      
      return $stmt->fetch()['old_pwd'];
    }
    catch(\PDOException $e) {
      return false;
    }
  }
  
  /**
  * @brief 패스워드 생성
  * @param string userpw
  * @return string sha256으로 암호화된 패스워드
  */
  private function __createPassword(string $str) : string
  {
    $planBytes = array_slice(unpack('c*',$str), 0); // 평문을 바이트 배열로 변환
    $ret = null;
    $bszChiperText = null;
    
    KISA_SEED_SHA256::SHA256_Encrypt($planBytes, count($planBytes), $bszChiperText);
    $r = count($bszChiperText);
    
    foreach($bszChiperText as $encryptedString) {
      $ret .= bin2hex(chr($encryptedString)); // 암호화된 16진수 스트링 추가 저장
    }
    return $ret;
  }
}