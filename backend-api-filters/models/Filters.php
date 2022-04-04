<?php

namespace Bankmedia\Models;

class Filters {
  protected $db;
  private $settings;

  private $tableSsomonHash = 'ssomon_hash';
  private $tableCopyContents = '_copy_contents';

  public function __construct($db) {
    $this->db = $db;
  }

  // ssomonhash 컨텐츠 파일별로 조회
  public function getSsomonHash(string $file_idx_list)
  {
    $query = <<<SQL
      SELECT
        `idx`,
        `copyid`,
        `type`,
        `fileidx` AS file_idx,
        `bbs_no` AS bbs_idx,
        `mureka_hash`,
        `md5sum`,
        `video_id`,
        `video_title`,
        `video_jejak_year`,
        `video_right_name`,
        `video_right_content_id`,
        `video_price`,
        `video_cha`,
        `video_osp_jibun`,
        `video_onair_date`,
        `video_right_id`,
        `regdate`
      FROM
        `{$this->tableSsomonHash}`
      WHERE
        1
SQL;

    $values = explode(',', $file_idx_list);
    $placeholders = rtrim(str_repeat('?,', count($values)), ',');

    if( true === isset($placeholders) ) {
      $query .= " AND fileidx IN ({$placeholders})";
    }

    $query .= ' ORDER BY idx ASC';

    try {
      $stmt = $this->db->prepare($query);
      $stmt->execute($values);

      return $stmt->fetchAll();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  // _copy_contents(제휴컨텐츠 목록) 제휴사 컨텐츠 아이디로 점검
  public function chkContentIdFile(string $contentid_list)
  {
    $values = explode(',', $contentid_list);
    $placeholders = rtrim(str_repeat('?,', count($values)), ',');

    $query = <<<SQL
      SELECT
        `idx`,
        `state`,
        `cate`,
        `copyid`,
        `copyname`,
        `title`,
        `inning`,
        `set_point`,
        `contents_id`,
        `averg`,
        `etc_info`,
        `opendate`,
        `regdate`
      FROM
        `{$this->tableCopyContents}`
      WHERE
        `state` = 3
SQL;
    if( true === isset($placeholders) ) {
      $query .= " AND contents_id IN ({$placeholders})";
    }

    try {
      $stmt = $this->db->prepare($query);
      $stmt->execute($values);

      return $stmt->fetchAll();
    }
    catch(\PDOException $e) {
      throw $e;
    }
  }

  /**
   * 모바일 필터링 키 메이커
   * @todo 임시로 여기에 작성, worker 구성되면 코드 이동
   */
  public function mobile_filterauthkey_maker(string $OSP_NAME, string $OSP_KEY, int $_NOW_TIME) : string
  {
    $key_value  = $OSP_NAME.$OSP_KEY.date("YmdHis", $_NOW_TIME);
    $key_md5    = md5($key_value);
    return $key_md5;
  }
}