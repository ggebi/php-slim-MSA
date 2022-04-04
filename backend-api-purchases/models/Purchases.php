<?php

namespace Bankmedia\Models;

/**
 * @file Purchases.php
 * @brief 구매목록 정보 조회 클래스
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @method countPurchases(array $options)
 * @method getPurchases(array $options)
 * @method putPurchase(array $member, array $content, array $pay_info)
 * @method getAccounts(string $userid)
 * @method delPurchases(string $userid, int $idx)
 * @method setPurchases(string $userid, int $idx)
 * @method saleLog(array $options)
 * @method cooperationLog(array $options)
 * @method convertExpireDate($date)
 * @method mobile_filtering_cpr_div(string $userid, int $now_time)
 */

class Purchases {
  protected $db;
  private $settings;
  private $default = [
    'limit' => 10,
    'offset' => 0,
  ];
  
  private $tablePurchases = '_purchase';
  private $tableAccount = '_account';
  private $tableSaleLog = '_seller_gain';
  private $tableCooperationLog = '_cooperation_log';
  private $tableEncodeComplete = 'encode_complete';
  private $tablePaymentCounter = 'sta_counter';

  public function __construct($db, $settings) {
    $this->db = $db;
    $this->settings = $settings;
  }
  
  /**
   * @brief 구매목록 개수 구하기
   * 
   * @param string userid
   * @param string category
   * @return int
   * @todo Database 분리 이전에는 임시로 encode_complete 테이블과 join을 통해서 모바일다운로드앱에서 사용할 수 있는 결과값만 추출
   *       현재 _purchase 테이블만 사용해서는 구분이 불가능
   */
  public function countPurchases(array $options) : int
  {
    $query = <<<SQL
    SELECT
      COUNT(DISTINCT(`p`.`bbs_no`)) AS `cnt`
    FROM
      `{$this->tablePurchases}` as `p`
      INNER JOIN `{$this->tableEncodeComplete}` as `e`
        ON `p`.`bbs_no` = `e`.`bbs_no`
    WHERE
      `p`.`userid` = :userid
      AND `p`.`state` = 0
      AND `p`.`expiredate` > UNIX_TIMESTAMP()
SQL;

    $options = array_merge($this->default, $options);

    if(isset($options['id'])) {
      $query .= " AND p.idx = :purchase_id";
    }

    if (isset($options['bbs_idx'])) {
      $query .= " AND p.bbs_no = :bbs_idx";
    }

    if (isset($options['category'])) {
      $query .= " AND p.code LIKE :category";
      $options['category'] .= '%';
		}

    // 사용하지는 않지만 기존 코드와 통일성을 위해서 구현
    $placeholders = array();
    if (count($this->settings['down_block_copyid']) ) {
			$placeholders = preg_filter('/^/', ':params_', array_values($this->settings['down_block_copyid']));
			
			$query .= " AND (p.copyid NOT IN (" . implode(',', $placeholders) . ") OR p.copyid IS NULL)";
    }

    // PC/모바일 구매 분리를 위해 추가
    $placeholders2 = array();
    if (count($this->settings['isPcMobileKindCopyid'])) {
      $placeholders2 = preg_filter('/^/', ':params_', array_values($this->settings['isPcMobileKindCopyid']));

      $query .= " AND (p.copyid NOT IN (" . implode(',', $placeholders2) . ") OR p.target = 2 OR p.copyid IS NULL)";
    }

    $query .= " LIMIT 1";
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR);

      isset($options['id']) and $stmt->bindParam(':purchase_id', $options['id'], \PDO::PARAM_INT);
      isset($options['bbs_idx']) and $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT);
      isset($options['category']) and $stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);

      // 사용하지는 않지만 기존 코드와 통일성을 위해서 구현
      if (count($this->settings['down_block_copyid'])) {
        foreach ($placeholders as $k => $v) {
          $stmt->bindParam($v, $this->settings['down_block_copyid'][$k]);
        }
      }
      // PC/모바일 구매 분리를 위해 추가
      if (count($this->settings['isPcMobileKindCopyid'])) {
        foreach ($placeholders2 as $k => $v) {
          $stmt->bindParam($v, $this->settings['isPcMobileKindCopyid'][$k]);
        }
      }
      $stmt->execute();

      return $stmt->fetch()['cnt'];
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  /**
   * @brief 구매목록 정보 조회
   * 
   * @param string userid
   * @param string category
   * @param int limit
   * @param int offset
   * @return array
   * @todo Database 분리 이전에는 임시로 encode_complete 테이블과 join을 통해서 모바일다운로드앱에서 사용할 수 있는 결과값만 추출
   *       현재 _purchase 테이블만 사용해서는 구분이 불가능
   */
  public function getPurchases(array $options) : array
  {
    $query = <<<SQL
    SELECT
      DISTINCT(`p`.`bbs_no`) AS `bbs_idx`,
      `p`.`idx`,
      -- `p`.`credit_check`,
      -- `p`.`target`,
      -- `p`.`billtype`,
      -- `p`.`point`,
      -- `p`.`packet`,
      -- `p`.`state`,
      -- `p`.`title`,
      -- `p`.`code`,
      -- `p`.`size`,
      -- `p`.`grade`,
      `p`.`userid`,
      `p`.`usernick`,
      -- `p`.`userid_seller`,
      -- `p`.`seller_nick`,
      `p`.`expiredate`,
      -- `p`.`regdate`,
      -- `p`.`link_mobile`,
      `p`.`downlod_count`,
      `p`.`downlod_count_m`,
      `p`.`stream_count`,
      `p`.`copyid`
      -- `p`.`ip`
    FROM
      `{$this->tablePurchases}` AS p
      INNER JOIN `{$this->tableEncodeComplete}` AS e
        ON `p`.`bbs_no` = `e`.`bbs_no`
    WHERE
      `p`.`userid` = :userid
      AND `p`.`state` = 0
      AND `p`.`expiredate` > UNIX_TIMESTAMP()
SQL;

    $options = array_merge($this->default, $options);

    if(isset($options['id'])) {
      $query .= " AND p.idx = :purchase_id";
    }

    if (isset($options['bbs_idx'])) {
      $query .= " AND p.bbs_no = :bbs_idx";
    }

    if(isset($options['category'])) {
      $query .= " AND p.code LIKE :category";
      $options['category'] .= '%';
		}

    // 사용하지는 않지만 기존 코드와 통일성을 위해서 구현
    $placeholders = array();
    if (count($this->settings['down_block_copyid'])) {
			$placeholders = preg_filter('/^/', ':params_', array_values($this->settings['down_block_copyid']));
			
			$query .= " AND (p.copyid NOT IN (" . implode(',', $placeholders) . ") OR p.copyid IS NULL)";
    }

    // PC/모바일 구매 분리를 위해 추가
    $placeholders2 = array();
    if (count($this->settings['isPcMobileKindCopyid'])) {
      $placeholders2 = preg_filter('/^/', ':params_', array_values($this->settings['isPcMobileKindCopyid']));

      $query .= " AND (p.copyid NOT IN (" . implode(',', $placeholders2) . ") OR p.target = 2 OR p.copyid IS NULL)";
    }
    
    $query .= " ORDER BY idx DESC";
    $query .= " LIMIT :limit OFFSET :offset";
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR);

      isset($options['id']) and $stmt->bindParam(':purchase_id', $options['id'], \PDO::PARAM_INT);
      isset($options['bbs_idx']) and $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT);
      isset($options['category']) and $stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);

      // 사용하지는 않지만 기존 코드와 통일성을 위해서 구현
      if (count($this->settings['down_block_copyid'])) {
        foreach ($placeholders as $k => $v) {
          $stmt->bindParam($v, $this->settings['down_block_copyid'][$k]);
        }
      }
      // PC/모바일 구매 분리를 위해 추가
      if (count($this->settings['isPcMobileKindCopyid'])) {
        foreach ($placeholders2 as $k => $v) {
          $stmt->bindParam($v, $this->settings['isPcMobileKindCopyid'][$k]);
        }
      }

      $stmt->bindParam(':limit', $options['limit'], \PDO::PARAM_INT);
      $stmt->bindParam(':offset', $options['offset'], \PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  public function putPurchase(array $pay_info)
  {

    //만료시간 계산
    $expire_day = 0;

    if( $pay_info['bill_type'] === 'J' ) { // 제휴컨텐츠
      if( in_array($pay_info['copyid'], $this->settings['expireTwoDayCopyid']) ) {
        $expire_day = 2;
      }
      // else if( in_array($pay_info['copyid'], $this->settings['expireOneDayCopyid']) ) { // 기타 필터링에 없는 제휴사와 expire_day 설정 값이 같으므로 임시 주석처리
      //   $expire_day = 1;
      // }
      else{
        $expire_day = 1;
      }
    }
    else {
      $expire_day = 2; //일반컨텐츠
    }

    //만화컨텐츠
    if( $pay_info['category'] == 'BD_DC' || $pay_info['category'] == 'BD_CT' || $pay_info['category'] == 'BD_IM') { // 제휴컨텐츠여도 만화면 만료일 7일??
      $expire_day = 7;
    }

    $expire_time = time() + ($expire_day * 86410);

    $query = <<<SQL
    INSERT INTO
      `{$this->tablePurchases}`
    SET
      `bbs_no` = :bbs_idx,
      `credit_check` = 0,
      `target` = 2,
      `billtype` = :billtype,
      `point` = :point,
      `packet` = :packet,
      `state` = 0,
      `title` = :title,
      `code` = :category,
      `size` = :size,
      `grade` = :grade, 
      `userid` = :userid,
      `usernick` = :nickname,
      `userid_seller` = :seller_id,
      `seller_nick` = :seller_nick,
      `expiredate` = :expire_time,
      `regdate` = :regdate,
      `link_mobile` = 1,
      `downlod_count_m` = 1,
      `copyid` = :copyid,
      `ip` = :ip
SQL;
    // downlod_count 테이블 컬럼명이 저렇게 들어가있음....
    try {
      $pay_info['bill_type'] = mb_convert_encoding($pay_info['bill_type'], "EUC-KR", "UTF-8");
      $pay_info['title'] = mb_convert_encoding($pay_info['title'], "EUC-KR", "UTF-8");
      $pay_info['category'] = mb_convert_encoding($pay_info['category'], "EUC-KR", "UTF-8");
      $pay_info['userid'] = mb_convert_encoding($pay_info['userid'], "EUC-KR", "UTF-8");
      $pay_info['nickname'] = mb_convert_encoding($pay_info['nickname'], "EUC-KR", "UTF-8");
      $pay_info['seller_id'] = mb_convert_encoding($pay_info['seller_id'], "EUC-KR", "UTF-8");
      $pay_info['seller_nick'] = mb_convert_encoding($pay_info['seller_nick'], "EUC-KR", "UTF-8");
      $pay_info['copyid'] = mb_convert_encoding($pay_info['copyid'], "EUC-KR", "UTF-8");
      $pay_info['client_ip'] = mb_convert_encoding($pay_info['client_ip'], "EUC-KR", "UTF-8");

      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':bbs_idx', $pay_info['bbs_idx'], \PDO::PARAM_INT);
      $stmt->bindParam(':billtype', $pay_info['bill_type'], \PDO::PARAM_STR);
      $stmt->bindParam(':point', $pay_info['point_use'], \PDO::PARAM_STR); //double 타입 체크가 없음
      $stmt->bindParam(':packet', $pay_info['packet_use'], \PDO::PARAM_STR); //double 타입 체크가 없음
      $stmt->bindParam(':title', $pay_info['title'], \PDO::PARAM_STR);
      $stmt->bindParam(':category', $pay_info['category'], \PDO::PARAM_STR);
      $stmt->bindParam(':size', $pay_info['size'], \PDO::PARAM_INT);
      $stmt->bindParam(':grade', $pay_info['grade'], \PDO::PARAM_INT);
      $stmt->bindParam(':userid', $pay_info['userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':nickname', $pay_info['nickname'], \PDO::PARAM_STR);
      $stmt->bindParam(':seller_id', $pay_info['seller_id'], \PDO::PARAM_STR);
      $stmt->bindParam(':seller_nick', $pay_info['seller_nick'], \PDO::PARAM_STR);
      $stmt->bindParam(':expire_time', $expire_time, \PDO::PARAM_INT);
      $stmt->bindParam(':regdate', $pay_info['regdate'], \PDO::PARAM_INT);
      $stmt->bindParam(':copyid', $pay_info['copyid'], \PDO::PARAM_STR);
      $stmt->bindParam(':ip', $pay_info['client_ip'], \PDO::PARAM_STR);

      $stmt->execute();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  public function getAccounts(string $userId) : array
  {
    $query = <<<SQL
      SELECT
        `idx`,
        `userid`,
        `method`,
        `billtype`,
        `cash`,
        `realpay`,
        `info`,
        `ip`,
        `regdate`,
        `refer`,
        `state`,
        `phone`,
        `paycode`,
        `calc_expect`,
        `partner`,
        `joinpath`,
        `pay_info_idx`,
        `pay_method_idx`,
        `pg_id`,
        `chk_sta`,
        `chk_iphone`
      FROM
        `{$this->tableAccount}`
      WHERE
        `userid` = :userid
SQL;

    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userId, \PDO::PARAM_STR);

      $stmt->execute();

      return $stmt->fetchAll();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  public function delPurchases(string $userid, string $idx_list) : bool
  {
    $values = explode(',', $idx_list);
    $placeholders = rtrim(str_repeat('?,', count($values)), ',');

    $query = <<<SQL
      UPDATE
        `{$this->tablePurchases}`
      SET
        `state` = 1
      WHERE
        `userid` = ?
        AND `idx` IN ({$placeholders})
        AND `state` = 0
SQL;
    
    try
    {
      $stmt = $this->db->prepare($query);
      $stmt->execute(array_merge(array($userid), $values));
      
      return 0 < $stmt->rowCount() ? true : false;
    }
    catch(\PDOException $e)
    {
      throw $e;
    }
  }

  public function setPurchases(string $userid, int $bbs_idx) : bool
  {
    $query = <<<SQL
      UPDATE
        `{$this->tablePurchases}`
      SET
        `downlod_count_m` = `downlod_count_m` + 1
      WHERE
        `userid` = :userid
          AND `idx` = :bbs_idx
      LIMIT 1
SQL;

    try {
      $userid = mb_convert_encoding($userid, "EUC-KR", "UTF-8");

      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->bindParam(':bbs_idx', $bbs_idx, \PDO::PARAM_INT);
      $stmt->execute();
      
      return 0 < $stmt->rowCount() ? true : false;
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  /**
   * 판매자 로그
   * @todo packet_have, point_have, coupon_have 현재 yesfile 소스코드에서 누락된건지 입력되고 있지 않았음. 판매자의 정보인지 구매자의 정보인지 파악해서 입력할건지 판단
   * @todo contents_id, copy_idx도 입력되고 있지 않았음. 컨텐츠 별로 로그를 남기는데, 해당 정보는 필터링사의 제휴컨텐츠 파일별 정보임
   */
  public function saleLog(array $options)
  {
    $query = <<<SQL
      INSERT INTO
        `{$this->tableSaleLog}`
      SET
        `userid` = :userid,
        `summary` = '',
        `recv_userid` = :recv_userid,
        `recv_nickname` = :recv_nickname,
        `bbs_no` = :bbs_idx,
        `title` = :title,
        `down_mode` = :down_mode,
        `packet_use` = :packet_use,
        `packet_save` = :packet_save,
        `packet_have` = :packet_have,
        `point_have` = :point_have,
        `coupon_have` = :coupon_have,
        `chk_iphone` = 1,
        `regdate` = :regdate,
        `copyid` = :copyid,
        `contents_id` = :contents_id,
        `copy_idx` = :copy_idx
SQL;
    
    try {
      $options['userid'] = mb_convert_encoding($options['userid'], "EUC-KR", "UTF-8");
      $options['recv_userid'] = mb_convert_encoding($options['recv_userid'], "EUC-KR", "UTF-8");
      $options['recv_nickname'] = mb_convert_encoding($options['recv_nickname'], "EUC-KR", "UTF-8");
      $options['title'] = mb_convert_encoding($options['title'], "EUC-KR", "UTF-8");
      $options['down_mode'] = mb_convert_encoding($options['down_mode'], "EUC-KR", "UTF-8");
      $options['packet_use'] = mb_convert_encoding($options['packet_use'], "EUC-KR", "UTF-8");
      $options['packet_save'] = mb_convert_encoding($options['packet_save'], "EUC-KR", "UTF-8");
      $options['packet_have'] = mb_convert_encoding($options['packet_have'], "EUC-KR", "UTF-8");
      $options['point_have'] = mb_convert_encoding($options['point_have'], "EUC-KR", "UTF-8");
      $options['coupon_have'] = mb_convert_encoding($options['coupon_have'], "EUC-KR", "UTF-8");
      $options['copyid'] = mb_convert_encoding($options['copyid'], "EUC-KR", "UTF-8");
      $options['contents_id'] = mb_convert_encoding($options['contents_id'], "EUC-KR", "UTF-8");

      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR); //판매자 아이디
      $stmt->bindParam(':recv_userid', $options['recv_userid'], \PDO::PARAM_STR); // 구매자 아이디
      $stmt->bindParam(':recv_nickname', $options['recv_nickname'], \PDO::PARAM_STR); // 구매자 닉네임
      $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT); // 컨텐츠 아이디
      $stmt->bindParam(':title', $options['title'], \PDO::PARAM_STR); // 컨텐츠 제목
      $stmt->bindParam(':down_mode', $options['down_mode'], \PDO::PARAM_STR); // 결제 유형
      $stmt->bindParam(':packet_use', $options['packet_use'], \PDO::PARAM_STR); // 컨텐츠 가격
      $stmt->bindParam(':packet_save', $options['packet_save'], \PDO::PARAM_STR); // 판매자 보상

      $stmt->bindParam(':packet_have', $options['packet_have'], \PDO::PARAM_STR); // 구매자 남은 캐쉬
      $stmt->bindParam(':point_have', $options['point_have'], \PDO::PARAM_STR); // 구매자 남은 포인트
      $stmt->bindParam(':coupon_have', $options['coupon_have'], \PDO::PARAM_INT);  // 구매자 남은 쿠폰
      $stmt->bindParam(':regdate', $options['regdate'], \PDO::PARAM_INT);
      $stmt->bindParam(':copyid', $options['copyid'], \PDO::PARAM_STR); //제휴사 아이디
      $stmt->bindParam(':contents_id', $options['contents_id'], \PDO::PARAM_STR);  //제휴사 컨텐츠 아이디
      $stmt->bindParam(':copy_idx', $options['copy_idx'], \PDO::PARAM_INT); // 제휴사 컨텐츠  idx

      $stmt->execute();
    }
    catch(\Exception $e) {
      throw $e;
    }
  }

  /**
   * 제휴 컨텐츠 파일 판매 로그
   */
  public function cooperationLog(array $options)
  {
    $query = <<<SQL
      INSERT INTO
        `{$this->tableCooperationLog}`
      SET
        `userid` = :userid,
        `copyid` = :copyid,
        `recv_userid` = :recv_userid,
        `recv_nickname` = :recv_nickname,
        -- `cp_userid` = :cp_userid,
        `bbs_no` = :bbs_idx,
        `title` = :title,
        `down_mode` = :down_mode,
        `contents_id` = :contents_id,
        `packet_use` = :packet_use,
        `packet_save` = :packet_save,
        `regdate` = :regdate,
        `cancel_date` = 0,
        `cpr_div` = :cpr_div,
        `chkiphone` = 1
SQL;

    try {
      $options['userid'] = mb_convert_encoding($options['userid'], "EUC-KR", "UTF-8");
      $options['copyid'] = mb_convert_encoding($options['copyid'], "EUC-KR", "UTF-8");
      $options['recv_userid'] = mb_convert_encoding($options['recv_userid'], "EUC-KR", "UTF-8");
      $options['recv_nickname'] = mb_convert_encoding($options['recv_nickname'], "EUC-KR", "UTF-8");
      $options['title'] = mb_convert_encoding($options['title'], "EUC-KR", "UTF-8");
      $options['down_mode'] = mb_convert_encoding($options['down_mode'], "EUC-KR", "UTF-8");
      $options['contents_id'] = mb_convert_encoding($options['contents_id'], "EUC-KR", "UTF-8");
      $options['packet_use'] = mb_convert_encoding($options['packet_use'], "EUC-KR", "UTF-8");
      $options['packet_save'] = mb_convert_encoding($options['packet_save'], "EUC-KR", "UTF-8");
      $options['cpr_div'] = mb_convert_encoding($options['cpr_div'], "EUC-KR", "UTF-8");
      
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['sellerid'], \PDO::PARAM_STR);
      $stmt->bindParam(':copyid', $options['copyid'], \PDO::PARAM_STR);
      $stmt->bindParam(':recv_userid', $options['recv_userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':recv_nickname', $options['recv_nickname'], \PDO::PARAM_STR);
      // $stmt->bindParam(':cp_userid', $options['cp_userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT);
      $stmt->bindParam(':title', $options['title'], \PDO::PARAM_STR);
      $stmt->bindParam(':down_mode', $options['down_mode'], \PDO::PARAM_STR);
      $stmt->bindParam(':contents_id', $options['contents_id'], \PDO::PARAM_STR);
      $stmt->bindParam(':packet_use', $options['packet_use'], \PDO::PARAM_STR);
      $stmt->bindParam(':packet_save', $options['packet_save'], \PDO::PARAM_STR);
      $stmt->bindParam('regdate', $options['regdate'], \PDO::PARAM_INT);
      $stmt->bindParam(':cpr_div', $options['cpr_div'], \PDO::PARAM_STR);

      $stmt->execute();
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }

  /**
   * 다운로드 통계 카운트 입력 및 업데이트
   */
  public function updatePurchasesCount(string $payment_place, int $use_point) : bool
  {
    $date = date('Y-m-d');

    $payment_place_field = array(
      'main_zzim' => ['h0', 'h1'],
      'play_zzim' => ['h2', 'h3'],
      'play_series' => ['h4', 'h5'],
      'play_movie' => ['h6', 'h7']
    );

    $place_field = $payment_place_field[$payment_place][0];
    $point_field = $payment_place_field[$payment_place][1];

    $query = <<<SQL
      INSERT INTO
        {$this->tablePaymentCounter}
      SET
        code = 'app_payment',
        kind = 1,
        sta_date = :date,
        {$place_field} = 1,
        {$point_field} = :point
      ON DUPLICATE KEY UPDATE
        code = 'app_payment',
        kind = 1,
        sta_date = :date,
        {$place_field} = {$place_field} + 1,
        {$point_field} = {$point_field} + :point
SQL;

    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':date', $date, \PDO::PARAM_STR);
      $stmt->bindParam(':point', $use_point, \PDO::PARAM_INT);
      $stmt->execute();

      $ret = $stmt->rowCount();
      return 0 < $ret ? true : false;
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }

  public function convertExpireDate($date) : string
  {
    $expire_date = $date - time();
    if( $expire_date < 3600 ) {
      return '0시간';
    }
    else {
      return floor($expire_date/3600)."시간";
    }
  }

  // 컨텐츠 구매 고유번호
  function mobile_filtering_cpr_div($userid, $now_time) : string
  {
    for ( $i=0, $cpr_chk_val=""; $i<3; $i++ ) {
      $cpr_chk_val .= chr(rand(65,91)); 
    }
    $cpr_div = $userid."_".$now_time."_".$cpr_chk_val;
    return $cpr_div;
  }
}