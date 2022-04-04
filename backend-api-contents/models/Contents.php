<?php

namespace Bankmedia\Models;

/**
 * @file Contents.php
 * 컨텐츠 정보 클래스
 * @author 주형우 (jhwmon@bankmedia.co.kr)
 * 
 * @method countContents(array $options)
 * @method getContents(array $options)
 * @method countContentFiles(int $bbs_idx)
 * @method getContentScore(int $bbs_idx)
 * @method getContentPrice(array $content)
 * @method getContentImage(array $content)
 * @method getContentJehyu(int $bbs_idx)
 * @method getEncodeFiles(array $options)
 * @method getFiles(array $options)
 * @method convertSize($size, $point = 0)
 * @method imageExtCheck($img_name)
 * @method countContentRecommendsMovie()
 * @method getContentRecommendsMovie(array $options)
 * @method getEpisode(string $contents_id)
 * @method getContentSeries(int $cidx, int $page, int $limit, string $sort = 'DESC')
 */
class Contents {
	protected $db;
	private $settings;
	private $default = [
		'limit' => 10,
		'offset' => 0,
	];
	
	private $tableContents = '_board';
	private $tableMobileImage = '_board_mobile_image_new';
	private $tableEncodeComplete = 'encode_complete';
	private $tableCategory = '_board_control';
	private $tableComments = '_board_comment';
	private $tableMd5sum = '_board_md5sum';
	private $tableCopyMd5sum = '_copy_md5sum';
	private $tableCopyContents = '_copy_contents';
	private $tableContentFiles = 'mmsv_webhard_file_bbs';
	private $tableContentFilesResolution = 'mmsv_webhard_file_resolution';
	private $tableContentRecommendsMovie = 'movie';
	private $tableVodContents = '_vod_contents';
	private $tableVodContentsSeries = '_vod_contents_series';
	private $tableVodContentList = '_vod_contents_bbs_list';
	
	public function __construct($db, $settings) {
		$this->db = $db;
		$this->settings = $settings;
	}

	/**
	 * @brief 컨텐츠 개수 구하기
	 * @param string id
	 * @param string category
	 * @return int
	 * @todo getContents 정보와 컨텐츠 총 숫자가 안맞을수가 있음
	 * 	getContents에서는 encode_complete와 INNER JOIN 후 데이터를 출력하기 때문에 encoding 되지 않은 파일(모바일 미지원) 
	 *  파일이 존재하면 문제여지가 있음. 데이터 테스트 과정에서 참고할 것.
	 */
	public function countContents(array $options) : int
	{
		$query = <<<SQL
			SELECT
				COUNT(`idx`) AS `total`
			FROM
				{$this->tableContents}
			WHERE
				1
SQL;

		if (isset($options['category'])) {
			$query .= " AND code_cate2 = :category";
		}
		else {
			$query .= " AND code_cate2 != :category";
			$options['category'] = 'BD_CT';
		}

		$placeholders = array();
		$values = array();
		if (isset($options['id'])) {
			$values = array_merge($values, explode(',', $options['id']));
			$placeholders = preg_filter('/^/', ':params_', array_values($values));
			
			$query .= " AND idx IN (" . implode(',', $placeholders) . ")";
		}

		try {
			$stmt = $this->db->prepare($query);

			$stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);

			foreach ($placeholders as $k => $v) {
				$stmt->bindParam($v, $values[$k]);
			}
			
			$stmt->execute();
			
			return $stmt->fetch()['total'];
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}

	/**
	 * @brief 컨텐츠 정보 조회
	 * @param string board_idx
	 * @param string category
	 * @param int limit
	 * @param int offset
	 * @return array
	 */
	public function getContents(array $options) : array
	{
		$query = <<<SQL
			SELECT
				`content`.`idx` AS `bbs_idx`,
				`content`.`title`,
				`content`.`userid`,
				`content`.`name` AS `nickname`,
				`content`.`size`,
				`content`.`chkcopy`,
				`content`.`code`,
				`content`.`code_cate2`,
				`content`.`point`,
				`content`.`contents`,
				`content`.`uploader_grade`,
				`content`.`chkiphone`,
				`content`.`regdate` as `regdate`,
				`category`.`title_cate2` AS `cate1_kr`,
				`category`.`title` AS `cate2_kr`,
				SUM(`encode`.`size`) AS `m_size`
			FROM
				`{$this->tableContents}` AS `content`
					INNER JOIN `{$this->tableCategory}` AS `category`
						ON `content`.`code` = `category`.`code`
							AND `content`.`code_cate2` = `category`.`code_cate2`
					INNER JOIN `{$this->tableEncodeComplete}` AS `encode`
						ON `content`.`idx` = `encode`.`bbs_no`
			WHERE
				1
SQL;

		$options = array_merge($this->default, $options);
		
		$placeholders = array();
		$values = array();
		if (isset($options['id'])) {
			$values = array_merge($values, explode(',', $options['id']));
			$placeholders = preg_filter('/^/', ':params_', array_values($values));
			
			$query .= " AND content.idx IN (" . implode(',', $placeholders) . ")";
		}
		
		if (isset($options['category'])) {
			$query .= " AND content.code_cate2 = :category";
		}
		else {
			$query .= " AND content.code_cate2 != :category";
			$options['category'] = 'BD_CT';
		}

		$query .= " GROUP BY content.idx ORDER BY content.idx ASC";
		$query .= " LIMIT :limit OFFSET :offset";

		try {
			$stmt = $this->db->prepare($query);
			foreach ($placeholders as $k => $v) {
				$stmt->bindParam($v, $values[$k]);
			}
			$stmt->bindParam(':category', $options['category'], \PDO::PARAM_STR);
			$stmt->bindParam(':limit', $options['limit'], \PDO::PARAM_INT);
			$stmt->bindParam(':offset', $options['offset'], \PDO::PARAM_INT);
			
			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}
	
	/**
	 * @brief 인코딩된 파일 개수 구하기
	 * @param int idx
	 * @return int
	 */
	public function countContentFiles(int $bbs_idx) : int
	{
		$query = <<<SQL
			SELECT
				COUNT(`bbs_no`) AS `cnt`
			FROM
				{$this->tableEncodeComplete}
			WHERE
				`bbs_no` = :bbs_idx
SQL;
		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':bbs_idx', $bbs_idx, \PDO::PARAM_INT);
			$stmt->execute();
			
			return $stmt->fetch()['cnt'];
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}
	
	/**
	 * @brief 댓글 평점, 갯수 구하기
	 * @param int idx
	 * @return array (댓글평점, 댓글개수)
	 */
	public function getContentScore(int $bbs_idx)
	{
		$query  = "SELECT AVG(grade) AS score, COUNT(grade) AS cnt FROM {$this->tableComments} WHERE link_idx = :bbs_idx";
		$query = <<<SQL
			SELECT
				AVG(`grade`) AS `score`,
				COUNT(`grade`) AS `cnt`
			FROM
				{$this->tableComments}
			WHERE
				`link_idx` = :bbs_idx
SQL;
		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':bbs_idx', $bbs_idx, \PDO::PARAM_INT);
			$stmt->execute();
			
			$data = $stmt->fetch();
			
			$data['score'] = round($data['score'], 2);
			$data['total'] = intval($data['cnt']);
			
			return $data;
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}
	
	/**
	 * @breif 컨텐츠 가격 구하기
	 * @param int idx
	 * @return int
	 */
	public function getContentPrice(array $content) : int
	{
		$point = 0;
		if (isset($content['chkcopy']) && ($content['chkcopy'] == 'Y')) {
			$query = <<<SQL
				SELECT
					`copyid`,
					SUM(`set_point`) AS `point`
				FROM
					`{$this->tableMd5sum}`
				WHERE
					`bbs_idx` = :bbs_idx
				GROUP BY
					`bbs_idx`
SQL;
			
			try {
				$stmt = $this->db->prepare($query);
				$stmt->bindParam(':bbs_idx', $content['bbs_idx'], \PDO::PARAM_INT);
				$stmt->execute();
				
				$data = $stmt->fetch();
				
				// 현재 사용하지 않지만 기능 구현만 해놓은 상태
				// if (in_array($data['copyid'], $this->settings['down_block_copyid'])) {
				// 	return false;
				// }

				$point = $data['point'];
			}
			catch (\PDOException $e) {
				throw $e;
			}
		}
		
		if (!$point && (isset($content['point']) && ($content['point'] > 0))) {
			$point = $content['point'];
		}
		
		if (!$point) {
			$point = round((ceil(($content['size'] / pow(1024, 2)) / 10))/10)*10;
		}
		
		if (($content['chkcopy'] == 'N') && in_array($content['code_cate2'], $this->settings['double_charge_cate'])) {
			$point *= 2;
		}


		if (
			isset($content['chkcopy'])
			&& ($content['chkcopy'] == 'N') // 비제휴 컨텐츠
			&& ($point < 50) // 포인트가 50 미만일 경우
		)
		{
			$point = 50;
		}
		
		return $point;
	}
		
	/**
	 * @brief 컨텐츠 이미지 링크 구하기
	 * @param 컨텐츠
	 * @return string 이미지 링크
	 */
	public function getContentImage(array $content)
	{
		$limit = 3;
		if (isset($content['code_cate2']) && ($content['code_cate2'] == "BD_AD")) {
			$content['contents'] = stripcslashes($content['contents']);
			$contents = str_replace("<img","||**||", $content['contents']);
			$contents = str_replace("<IMG","||**||", $contents);
			$contents = str_replace("this.src=","", $contents);
			$str_arr  = explode("||**||", $contents);
			
			$img_list = array();
			for ($i = 1; $i < count($str_arr); $i++) {
				$str_rep    = str_replace("\\","", $str_arr[$i]);
				$str_rep    = str_replace("\"","'", $str_rep);
				$img_arr    = explode("src='", $str_rep);
				$img_arr2   = explode("'", $img_arr[1]);
				if (!trim($img_arr2[0])) {
					$img_arr    = explode("src=", $str_rep);
					$img_arr2   = explode("/>", $img_arr[1]);
				}
				$img_arr2[0] = str_replace(array('`','>'),'', strip_tags($img_arr2[0]));
				if ($this->imageExtCheck(trim($img_arr2[0]))){
					$img_list['lists'][]['imagename'] = trim($img_arr2[0]);
				}
				if (count($img_list['lists']) >= $limit) {
					break;
				}
			}
			
			if (count($img_list) == 0) {
				return "";
			}
			return $img_list['lists'][count($img_list['lists']) - 1]['imagename'];
		}
		else {
			$query = <<<SQL
				SELECT
					`idx`,
					`state`,
					`kind`,
					`board_mobile_idx`,
					`pos`,
					`image_domain`,
					`image_dir`,
					`image_name`,
					`original_board_idx` AS `bbs_idx`
				FROM
					`{$this->tableMobileImage}`
						USE INDEX (`original_board_idx`)
				WHERE
					`original_board_idx` = :bbs_idx
						AND	`kind` = 3
				LIMIT
					1
SQL;

			try {
				$stmt = $this->db->prepare($query);
				$stmt->bindParam(':bbs_idx', $content['bbs_idx'], \PDO::PARAM_INT);
				$stmt->execute();
				
				$data = $stmt->fetch();
				return $data['image_domain'].$data['image_dir'].$data['image_name'];
			}
			catch(\PDOException $e) {
				throw $e;
			}
		}
	}

	public function getContentJehyu(int $bbs_idx) : array
	{
		
		$query = <<<SQL
			SELECT
				`a`.`idx`,
				`a`.`copyid`,
				`a`.`set_point`,
				-- `a`.`bbs_idx`,
				`a`.`file_bbs_no`AS `file_idx`,
				-- `a`.`use_chk`,
				-- `a`.`down_chk`,
				-- `a`.`copyno`,
				`a`.`contents_id`,
				`b`.`summary`
				-- `b`.`inning`
			FROM
				`{$this->tableMd5sum}` AS `a`
					INNER JOIN `{$this->tableCopyMd5sum}` AS `b`
						ON (`a`.`copyno` = `b`.`idx`)
			WHERE
				`a`.`bbs_idx` = :bbs_idx
SQL;
		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':bbs_idx', $bbs_idx, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}

	/**
	 * 인코딩 파일 정보 조회
	 * @param 
	 * [
	 *   'id' = 컨텐츠 아이디(필수)
	 *   'file_idx' = 컨텐츠 파일 아이디(선택)
	 * ]
	 */
	public function getEncodeFiles(array $options) : array
	{
		$query = <<<SQL
			SELECT
				`idx`,
				-- `org_md5sum`,
				`org_file_idx` AS `file_idx`,
				-- `enc_md5sum`,
				-- `userid`,
				`bbs_no` AS `bbs_idx`,
				-- `uidx`,
				-- `depth`,
				-- `foldername`,
				`realname`,
				`size`,
				-- `realupload`,
				-- `temp_volume`,
				-- `ori_volume`,
				-- `volume_volume`,
				`temp_name`
				-- `regdate`,
				-- `regdate2`,
				-- `etkey`,
				-- `m_high`,
				-- `contents_id`,
				-- `clip`
			FROM
				`{$this->tableEncodeComplete}`
			WHERE
				`bbs_no` = :bbs_idx
SQL;

		isset($options['file_idx']) and $query .= ' AND org_file_idx = :file_idx';
		$query .= ' ORDER BY org_file_idx ASC';

		$options = array_merge($this->default, $options);

		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':bbs_idx', $options['id'], \PDO::PARAM_INT);
			isset($options['file_idx']) and $stmt->bindParam(':file_idx', $options['file_idx'], \PDO::PARAM_INT);

			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}

	// mmsv_webhard_file_bbs
	public function getFiles(array $options) : array
	{
// 		$query = <<<SQL
// 			SELECT
// 				`f`.`no` AS `file_idx`,
// 				`f`.`userid`,
// 				`f`.`bbs_no` AS `bbs_idx`,
// 				`f`.`idx`,
// 				`f`.`depth`,
// 				`f`.`foldername`,
// 				`f`.`realname`,
// 				`f`.`size`,
// 				`f`.`realsize`,
// 				`f`.`count_fix`,
// 				`f`.`count_packet`,
// 				`f`.`md5sum`,
// 				`f`.`upload_date`,
// 				`f`.`flag_upload`,
// 				`f`.`flag_realupload`,
// 				`f`.`flag_warn`,
// 				`f`.`temp_volume`,
// 				`f`.`temp_name`,
// 				`f`.`flag_exist`,
// 				`f`.`encoding`,
// 				`r`.`resolution_w`,
// 				`r`.`resolution_h`,
// 				`r`.`codec`,
// 				`r`.`frame`,
// 				`r`.`play_time`,
// 				`r`.`screen_ratio`,
// 				`r`.`picture_info1`
// 			FROM
// 				`{$this->tableContentFiles}` AS `f`
// 					LEFT JOIN `{$this->tableContentFilesResolution}` AS `r`
// 						ON ( `f`.`no` = `r`.`no` )
// 			WHERE
// 				1
// SQL;
		$query = <<<SQL
		SELECT
			`f`.`no` AS `file_idx`,
			`f`.`realname`,
			`f`.`size`,
			`f`.`realsize`,
			`f`.`upload_date`,
			`f`.`temp_name`,
			`r`.`resolution_w`,
			`r`.`resolution_h`,
			`r`.`play_time`
		FROM
			`{$this->tableContentFiles}` AS `f`
				LEFT JOIN `{$this->tableContentFilesResolution}` AS `r`
					ON ( `f`.`no` = `r`.`no` )
		WHERE
			1
SQL;

		$placeholders = array();
		$values = array();
		if (isset($options['file_idx_list'])) {
			$values = array_merge($values, explode(',', $options['file_idx_list']));
			$placeholders = preg_filter('/^/', ':params_', array_values($values));
			
			$query .= " AND f.no IN (" . implode(',', $placeholders) . ")";
		}

		if (isset($options['bbs_idx'])) {
			$query .= " AND bbs_no = :bbs_idx";
		}

		$query .= " ORDER BY f.no ASC";

		try {
			$stmt = $this->db->prepare($query);
			foreach ($placeholders as $k => $v) {
				$stmt->bindParam($v, $values[$k], \PDO::PARAM_INT);
			}
			isset($options['bbs_idx']) and $stmt->bindParam(':bbs_idx', $options['bbs_idx'], \PDO::PARAM_INT);

			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch(\PDOException $e) {
			throw $e;
		}
	}

	public function convertSize($size, $point = 0){
		if (empty($size)) return '0B';
		if ($size / 1024 < 1) return $size.'B';
		else if ($size / pow(1024, 2) < 1) return round($size / 1024, 0).'K';
		else if ($size / pow(1024, 3) < 1) return round($size / pow(1024, 2), 0).'M';
		else if ($size / pow(1024, 4) < 1) return round($size / pow(1024, 3), 1).'G';
		else return round($size / pow(1024, 4), 1).'T';
	}

	private function imageExtCheck($img_name){
		$img_ext_arr = array('bmp','jpeg','jpg','gif','png');
		$name_arr = explode(".",$img_name);
		$ext = strtolower($name_arr[(count($name_arr) - 1)]);
		if (in_array($ext,$img_ext_arr)){
			return true;
		}else{
			return false;
		}
	}

	public function category_choice_shot($code) : int
	{
		$category_arr = array();
		$category_arr['BD_MV'] = 2;
		$category_arr['BD_DM'] = 3;
		$category_arr['BD_UC'] = 4;
		$category_arr['BD_AN'] = 5;
		$category_arr['BD_AD'] = 6;
		$category_arr['BD_EC'] = 52;
		$category_arr['BD_CT'] = 63;
		$category_arr['BD_IM'] = 84;
		$category_arr['BD_DC'] = 89;

		$ret = $category_arr[$code];

		if (isset($ret)) {
			return $ret;
		} else {
			// 매칭되는 카테고리 정보가 없을 경우 디폴트 1로 설정
			return 1;
		}
	}

	public function countContentRecommendsMovie()
	{
		// FIXME: 기획팀 요청으로 인코딩 안되어있는 파일은 리스트에서 제거하도록 조치
		//				향후 app 이외에 website에서 호출 할 때 참고.
		$query = <<<SQL
			SELECT
				count(`movie`.`represent_idx`) AS `total`
			FROM
				`{$this->tableContentRecommendsMovie}` AS `movie`
				INNER JOIN `{$this->tableMd5sum}` AS `md5sum`
					ON `movie`.`represent_idx` = `md5sum`.`bbs_idx`
				INNER JOIN `{$this->tableEncodeComplete}` AS `encode`
					ON `movie`.`represent_idx` = `encode`.`bbs_no`
			WHERE
				`movie`.`represent_idx` != 0
				AND `movie`.`status` = 'Y'
SQL;
		$placeholders = array();
		if (count($this->settings['mobileDnNotCopyid'])) {
			$placeholders = preg_filter('/^/', ':params_', array_values($this->settings['mobileDnNotCopyid']));
			
			$query .= " AND (md5sum.copyid NOT IN (" . implode(',', $placeholders) . "))";
		}

		try {
			$stmt = $this->db->prepare($query);

			if (count($this->settings['mobileDnNotCopyid'])) {
        foreach ($placeholders as $k => $v) {
          $stmt->bindParam($v, $this->settings['mobileDnNotCopyid'][$k]);
        }
			}

			$stmt->execute();

			return $stmt->fetch()['total'];
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}
	
	public function getContentRecommendsMovie(array $options) : array
	{
		// FIXME: 기획팀 요청으로 인코딩 안되어있는 파일은 리스트에서 제거하도록 조치
		//				향후 app 이외에 website에서 호출 할 때 참고.
		$query = <<<SQL
			SELECT
				`movie`.`represent_idx` AS `bbs_idx`,
				`movie`.`title` AS `title`,
				`movie`.`thumbnail` AS `poster_img`,
				`movie`.`pub_date` AS `date`
			FROM
				`{$this->tableContentRecommendsMovie}` AS `movie`
				INNER JOIN `{$this->tableMd5sum}` AS `md5sum`
					ON `movie`.`represent_idx` = `md5sum`.`bbs_idx`
				INNER JOIN `{$this->tableEncodeComplete}` AS `encode`
					ON `movie`.`represent_idx` = `encode`.`bbs_no`
			WHERE
				`movie`.`represent_idx` != 0
				AND `movie`.`status` = 'Y'
SQL;

		//FIXME: 정상 작동하는지 나중에 테스트 필요(현재는 컨트롤에서 options의 default값을 설정해서 보내주도록 구현)
		$options = array_merge($this->default, $options);

		$placeholders = array();
		if (count($this->settings['mobileDnNotCopyid'])) {
			$placeholders = preg_filter('/^/', ':params_', array_values($this->settings['mobileDnNotCopyid']));
			
			$query .= " AND (md5sum.copyid NOT IN (" . implode(',', $placeholders) . "))";
		}

		$query .= ' ORDER BY movie.sorting DESC';
		$query .= ' LIMIT :limit OFFSET :offset';

		try {
			$stmt = $this->db->prepare($query);

			if (count($this->settings['mobileDnNotCopyid'])) {
        foreach ($placeholders as $k => $v) {
          $stmt->bindParam($v, $this->settings['mobileDnNotCopyid'][$k]);
        }
			}
			
			$stmt->bindParam(':limit', $options['limit'], \PDO::PARAM_INT);
			$stmt->bindParam(':offset', $options['offset'], \PDO::PARAM_INT);

			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}

	public function getEpisode(string $contents_id) : array
	{
		$query = <<<SQL
			SELECT
				`s`.`inning` as episode,
				`c`.`cidx`,
				`c`.`title`,
				`c`.`broadcaster`,
				`c`.`show_age`,
				`c`.`poster_mobile`
			FROM
				`{$this->tableVodContentsSeries}` as `s`
				LEFT JOIN `{$this->tableVodContents}` as `c`
					ON `s`.`cidx` = `c`.`cidx`
			WHERE
				`s`.`contents_id` = :contents_id
				AND `c`.`state` = 1
			LIMIT
				1
SQL;

		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':contents_id', $contents_id, \PDO::PARAM_STR);
			$stmt->execute();

			return $stmt->fetch();
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}

	public function countTotalEpisode(int $cidx) : int
	{
		$query = <<<SQL
			SELECT
				COUNT(DISTINCT(`s`.`sidx`)) AS `cnt`
			FROM
				`{$this->tableVodContentsSeries}` AS `s`
				LEFT JOIN `{$this->tableVodContentList}` AS `b`
					ON `s`.`sidx` = `b`.`sidx`
			WHERE
				`s`.`cidx` = :cidx
				AND `s`.`list_update` > 1000
				AND `b`.`bbs_idx` IS NOT NULL
SQL;

		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':cidx', $cidx, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetch()['cnt'];
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}

	public function countEpisode(int $cidx, int $episode) : int
	{
		$query = <<<SQL
			SELECT
				COUNT(DISTINCT(s.sidx)) AS cnt
			FROM
				{$this->tableVodContentsSeries} AS s
				LEFT JOIN {$this->tableVodContentList} AS b
					ON s.sidx = b.sidx
			WHERE
				s.cidx = :cidx
				AND b.bbs_idx IS NOT NULL
				AND s.inning <= :inning
SQL;

		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':cidx', $cidx, \PDO::PARAM_INT);
			$stmt->bindParam(':inning', $episode, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetch()['cnt'];
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}

	public function getContentSeries(int $cidx, int $page, int $limit, string $sort = 'DESC') : array
	{
		$query = <<<SQL
			SELECT
				s.inning as episode,
				-- s.inning_str,
				s.contents_id,
				s.title_img,
				s.opendate,
				v.bbs_idx
			FROM
				_vod_contents_series AS s
				LEFT JOIN _vod_contents_bbs_list AS v
					ON s.sidx = v.sidx
			WHERE
				s.cidx = :cidx
				AND s.list_update > 1000
				AND v.bbs_idx IS NOT NULL
			GROUP BY
				s.inning
SQL;
		$query .= ' ORDER BY s.inning '.$sort;
		$query .= ' LIMIT :page, :limit';
		
		try {
			$stmt = $this->db->prepare($query);
			$stmt->bindParam(':cidx', $cidx, \PDO::PARAM_INT);
			$page_offset = ($page - 1) * $limit;
			$stmt->bindParam(':page', $page_offset, \PDO::PARAM_INT);
			$stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
			$stmt->execute();

			return $stmt->fetchAll();
		}
		catch (\PDOException $e) {
			throw $e;
		}
	}
}