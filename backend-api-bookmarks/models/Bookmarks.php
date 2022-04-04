<?php

namespace Bankmedia\Models;

/**
 * @file Bookmarks.php
 * @brief 찜목록을 조회,삭제하는 클래스
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @method countBookmarks(array $options) : int
 * @method getBookmarks(array $options) : array
 * @method delBookmarks(string $userid, int $id) : bool
 * @method putBookmarks()
 */
class Bookmarks {
  protected $db;
  private $default = [
    'limit' => 10,
    'offset' => 0,
  ];

  private $tableBookmarks = '_board_bookmark';
  private $tableEncodeComplete = 'encode_complete';

  public function __construct($db) {
    $this->db = $db;
  }
  
  /**
   * @brief 찜목록 갯수 구하기
   * @param string userid
   * @param string category
   * @return int
   * TODO: Database 분리 이전에는 임시로 encode_complete 테이블과 join을 통해서 모바일다운로드앱에서 사용할 수 있는 결과값만 추출
   *       현재 _bookmarks 테이블만 사용해서는 구분이 불가능
   */
  public function countBookmarks(array $options) : int
  {
    $query = <<<SQL
      SELECT
        COUNT(DISTINCT(`b`.`bbs_no`)) AS `cnt`
      FROM
        `{$this->tableBookmarks}` as `b`
        INNER JOIN `{$this->tableEncodeComplete}` as `e`
          ON `b`.`bbs_no` = `e`.`bbs_no`
      WHERE
        `b`.`userid` = :userid
        AND `b`.`link_mobile` = 1
SQL;

    $options = array_merge($this->default, $options);

    if (isset($options['category'])) {
      $query .= " AND b.code LIKE :category";
      $options['category'] .= '%';
		}
		else {
			$query .= " AND b.code != :category";
      $options['category'] = 'BD_CT_11';
    }
    
    if (isset($options['bbs_idx'])) {
      $query .= " AND b.bbs_no = :bbs_idx";
    }

    $query .= " LIMIT 1";
    
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);
      isset($options['bbs_idx']) and $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT);

      $stmt->execute();

      return $stmt->fetch()['cnt'];
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }

  /**
   * @brief 찜목록 리스트 조회
   * 
   * @param string userid
   * @param string category
   * @param int limit
   * @param int offset
   * @return array
   * @todo Database 분리 이전에는 임시로 encode_complete 테이블과 join을 통해서 모바일다운로드앱에서 사용할 수 있는 결과값만 추출
   *       현재 _bookmarks 테이블만 사용해서는 구분이 불가능
   */
  public function getBookmarks(array $options) : array
  {
    $query = <<<SQL
      SELECT
        DISTINCT(`b`.`bbs_no`) AS `bbs_idx`,
        `b`.`idx`,
        `b`.`userid`
        -- `b`.`title`,
        -- `b`.`size`,
        -- `b`.`code`,
        -- `b`.`uploader`
      FROM
        `{$this->tableBookmarks}` AS b
        INNER JOIN `{$this->tableEncodeComplete}` AS p
          ON `b`.`bbs_no` = `p`.`bbs_no`
      WHERE
        `b`.`userid` = :userid
        AND `b`.`link_mobile` = 1
SQL;

    $options = array_merge($this->default, $options);

    if (isset($options['category'])) {
      $query .= " AND code LIKE :category";
      $options['category'] .= '%';
		}
		else {
			$query .= " AND code != :category";
      $options['category'] = 'BD_CT_11';
		}
    
    $query .= " ORDER BY idx DESC";
    $query .= " LIMIT :limit OFFSET :offset";
    
    // 완성된 example query 주석 남겨놓으면 좋을듯
    try {
      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $options['userid'], \PDO::PARAM_STR);
      $stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);
      $stmt->bindParam(':limit', $options['limit'], \PDO::PARAM_INT);
      $stmt->bindParam(':offset', $options['offset'], \PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll();
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }
  
  /**
   * @brief 찜목록 삭제
   * @param string userid
   * @param int idx
   * @return bool
   */
  public function delBookmarks(string $userid, string $id) : bool
  {
    $values = explode(',', $id);
    $limit = count($values);
    $placeholders = rtrim(str_repeat('?,', count($values)), ',');
    
    $query = <<<SQL
      DELETE FROM
        `{$this->tableBookmarks}`
      WHERE
        `userid` = ?
        AND `idx` IN ({$placeholders})
      LIMIT {$limit}
SQL;
    try
    {
      $stmt = $this->db->prepare($query);
      $stmt->execute(array_merge(array($userid), $values));
      
      return 0 < $stmt->rowCount() ? true : false;
    }
    catch (\PDOException $e)
    {
      throw $e;
    }
  }

  /**
   * 임시 테스트용 북마크 추가
   */
  public function putBookmarks(string $userid, array $content)
  {
    $query = <<<SQL
      INSERT INTO `{$this->tableBookmarks}`
      SET
        `userid` = :userid,
        `bbs_no` = :bbs_idx,
        `title` = :title,
        `size` = :size,
        `code` = :code,
        `uploader` = :uploader,
        `link_mobile` = 1,
        `chk_iphone` = :chkiphone,
        `regdate` = UNIX_TIMESTAMP()
SQL;

    try {
      $userid = mb_convert_encoding($userid, "EUC-KR", "UTF-8");
      $content['title'] = mb_convert_encoding($content['title'], "EUC-KR", "UTF-8");
      $content['code'] = mb_convert_encoding($content['code'], "EUC-KR", "UTF-8");
      $content['uploader'] = mb_convert_encoding($content['uploader'], "EUC-KR", "UTF-8");

      $stmt = $this->db->prepare($query);
      $stmt->bindParam(':userid', $userid, \PDO::PARAM_STR);
      $stmt->bindParam(':bbs_idx', $content['bbs_idx'], \PDO::PARAM_INT);
      $stmt->bindParam(':title', $content['title'], \PDO::PARAM_STR);
      $stmt->bindParam(':size', $content['size'], \PDO::PARAM_INT);
      $stmt->bindParam(':code', $content['code'], \PDO::PARAM_STR);
      $stmt->bindParam(':uploader', $content['uploader'], \PDO::PARAM_STR);
      $stmt->bindParam(':chkiphone', $content['chkiphone'], \PDO::PARAM_INT);

      $stmt->execute();
    }
    catch (\PDOException $e) {
      throw $e;
    }
  }
}