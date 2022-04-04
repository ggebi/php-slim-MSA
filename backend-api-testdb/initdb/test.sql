/*
SQLyog Community v12.5.0 (32 bit)
MySQL - 5.0.33-log : Database - webhard
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`webhard` /*!40100 DEFAULT CHARACTER SET euckr */;

use `webhard`;

/*Table structure for table `_account` */
/*
CREATE TABLE `_autopay_cancel` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `kind` varchar(20) NOT NULL COMMENT '결제 종류',
  `action` tinyint(1) NOT NULL default 0 COMMENT '0-cancel, 1-delete, 2-recovery',
  `userid` varchar(20) NOT NULL COMMENT '사용자아이디',
  `register` varchar(30) NOT NULL default '' COMMENT '들록한 관리자이름',
  `pos` tinyint(1) NOT NULL default 0 COMMENT '해지하기 요청 경로 0-관리자페이지, 1-웹, 2-모바일',
  `regdate` int(10) unsigned NOT NULL COMMENT '등록일자',
  PRIMARY KEY (`idx`),
  KEY `action` (`action`),
  KEY `userid` (`userid`),
  KEY `pos` (`pos`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;
*/

/*
CREATE TABLE `bonus_point_expire_fixlog` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL COMMENT '사용자아이디',
  `before_point` int(1) NOT NULL default 0 COMMENT '수정 이전 포인트',
  `after_point` int(1) NOT NULL default 0 COMMENT '수정 이후 포인트',
  `regdate` int(1) NOT NULL default 0 COMMENT '등록일자',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2018.12 보너스포인트 누락된 정조 재조정 임시로그 테이블';
*/
/*
CREATE TABLE `ad_domain_join` (
  `idx` int(1) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL DEFAULT '' COMMENT '사용자아이디',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2018.12 광고도메인 가입여부 확인 테이블';
*/

/*
CREATE TABLE `member_leave_withdraw_log` (
  `idx` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL DEFAULT '' COMMENT '사용자아이디',
  `adult_hash` varchar(64) NOT NULL DEFAULT '' COMMENT '중복인증 여부 확인 코드',
  `leave_withdraw` tinyint(1) NOT NULL DEFAULT 1 COMMENT '탈퇴방어 성공유무 1:성공 2:실패',
  `chk_auth` tinyint(1) NOT NULL DEFAULT 1 COMMENT '본인인증 여부 1:인증 2:미인증',
  `give_point` int(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '지급 포인트',
  `give_bonus_point` int(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '지급 보너스 포인트',
  `cnt_account` int(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '결제건수',
  `is_mobile` tinyint(1) NOT NULL DEFAULT 1 COMMENT '기기 1:pc 2:mobile',
  `last_login` int(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '마지막 로그인 시간',
  `regdate` int(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '탈퇴시도일',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`),
  KEY `regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2018.12 탈퇴방어 성공확인 로그 테이블';
*/
/*
CREATE TABLE `click_cnt_stats` (
 `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
 `log_date` DATE NOT NULL DEFAULT '1000-01-01' COMMENT '날짜',
 `cate_name` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '대분류 이름',
 `menu_name` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '구분 이름',
 `click_page` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '요청 페이지 식별 아이디',
 `click_id` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '요청 클릭 위치 식별 아이디',
 `click_cnt` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '요청 클릭 카운트',
 PRIMARY KEY (`idx`),
 KEY `click_cnt` (`click_page`, `click_id`, `log_date`),
 KEY `log_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.01 클릭통계 카운트 테이블';

CREATE TABLE `cantv_member` (
  `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '유저아이디',
  `user_key` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '1회용 canTv 로그인 인증 key',
  `is_mobile` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '기기[0:웹 1:모바일]',
  `last_login` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '마지막 로그인 일자',
  `regdate` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'canTv 전용 회원정보 설정 일자',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`),
  KEY `user_key` (`user_key`),
  KEY `regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.01 canTv 회원정보';

CREATE TABLE `cantv_purchase` (
  `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '상태[1:정상 2:취소환불등]',
  `userid` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '유저아이디',
  `l_code` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '방송코드',
  `l_title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '방송명',
  `b_idx` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '방송자 고유키',
  `b_id` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '방송자 아이디',
  `b_nick` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '방송자 닉네임',
  `t_idx` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '거래 요청키',
  `cut_point` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '사용 포인트',
  `is_mobile` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '기기[0:웹 1:모바일]',
  `regdate` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '등록일자',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`),
  KEY `regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.01 canTv 구매 정보';
*/


-- CREATE TABLE `cron_monitoring_list` (
--   `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
--   `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '상태[1:정상 2:중지]',
--   `alert` TINYINT(1) UNSIGNED NOT NULL DEFAULT 2 COMMENT '알람 사용 여부[1:사용 2:미사용]',
--   `title` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '제목',
--   `info` TEXT NOT NULL DEFAULT '' COMMENT '상세정보',
--   `file_path` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '파일경로',
--   `cycle` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '실행주기(crontab 형식)',
--   `alert_delay_time` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '경고 알림 수동 지연시간(초 단위)',
--   `priority` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '우선순위 정렬[0~255]',
--   `execution_time` INT(1) NOT NULL DEFAULT 0 COMMENT '최종 실행시간',
--   `regdate` INT(1) NOT NULL DEFAULT 0 COMMENT '등록시간',
--   PRIMARY KEY (`idx`),
--   KEY `alert` (`alert`),
--   KEY `priority` (`priority`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.02 크론 모니터링';

-- CREATE TABLE `bonus_point_expire_fixlog` (
--   `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
--   `userid` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사용자 아이디',
--   `bonus_point` INT(1) NOT NULL DEFAULT 0 COMMENT '보유 보너스 포인트',
--   `before_point_expire` INT(1) NOT NULL DEFAULT 0 COMMENT '수정 이전 포인트만료정보',
--   `after_point_expire` INT(1) NOT NULL DEFAULT 0 COMMENT '수정 이후 포인트만료정보',
--   `chk_iphone` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1:pc 2:mobile',
--   `regdate` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '등록일자',
--   PRIMARY KEY (`idx`),
--   KEY `userid` (`userid`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.03 보너스포인트 만료정보 재조정 로그 테이블';

-- insert into _gift_expier_user set `type` = 2, `state` = 1, `userid` = \'jmonaco88\', `nickname` = \'adbddwqw1\', `gift_mult_cno` = \'\', `gift_mult_cno2` = \'\', `cash` = 0, `point` = 5000, `method` = \'\', `info` = \'마일리지 전환\', `coupon` = 0, `expier_date` = \'2019-04-11\', `regdate` = 1552380093, `minus_point` = 0, `remain_point` = 0, `chk_iphone` = 0;


CREATE TABLE `bonus_point_expire_delete_log` (
  `idx` INT(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userid` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사용자 아이디',
  `have_bonuspoint` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '보유하고 있던 보너스포인트',
  `expire_bonuspoint` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '만료 된 차감 보너스포인트',
  `remain_bonuspoint` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '만료차감 이후 보유하고 있는 보너스포인트',
  `regdate` INT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '등록일자',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`),
  KEY `regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2019.03 만료된 보너스포인트 삭제 로그 테이블';


/*
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '충전샵', 1, 1, '2019-01-17');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '무료충전', 1, 2, '2019-01-17');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '이벤트', 1, 3, '2019-01-17');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '충전샵', 2, 1, '2019-01-17');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '전체메뉴', 2, 4, '2019-01-17');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '검색창', 2, 5, '2019-01-17');

INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '충전샵', 1, 1, '2019-01-18');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '무료충전', 1, 2, '2019-01-18');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '이벤트', 1, 3, '2019-01-18');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '충전샵', 2, 1, '2019-01-18');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '전체메뉴', 2, 4, '2019-01-18');
INSERT INTO `click_cnt_stats` (`cate_name`, `menu_name`, `click_page`, `click_id`, `log_date`) VALUES ('상단메뉴', '검색창', 2, 5, '2019-01-18');

INSERT INTO `click_cnt_stats` (`click_page`, `click_id`) VALUES (2, 6);
*/
/*
CREATE TABLE `ad_domain_join` (
  `idx` int(1) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL COMMENT '사용자아이디',
  `regdate` int(1) NOT NULL COMMENT '등록일자',
  PRIMARY KEY (`idx`),
  KEY `userid` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT '2018.12 광고도메인 가입여부 확인 테이블';
*/

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;