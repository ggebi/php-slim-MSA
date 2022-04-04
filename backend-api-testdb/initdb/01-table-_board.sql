-- MySQL dump 10.11
--
-- Host: localhost    Database: webhard
-- ------------------------------------------------------
-- Server version	5.0.33-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

use `webhard`;

--
-- Table structure for table `_board`
--

DROP TABLE IF EXISTS `_board`;
CREATE TABLE `_board` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `code` varchar(12) NOT NULL,
  `code_cate1` varchar(4) NOT NULL,
  `code_cate2` varchar(7) NOT NULL,
  `code_cate3` varchar(10) NOT NULL,
  `code_cate4` varchar(10) NOT NULL,
  `userid` varchar(20) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  `level` tinyint(3) unsigned NOT NULL default '0',
  `subject` varchar(10) NOT NULL,
  `title` varchar(200) NOT NULL default '',
  `contents` longtext NOT NULL,
  `thread` int(10) unsigned NOT NULL default '0',
  `pos` decimal(20,4) NOT NULL default '0.0000',
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `ip` varchar(20) NOT NULL default '',
  `point` int(10) unsigned NOT NULL default '0',
  `photo` enum('Y','N') NOT NULL default 'N',
  `period` int(10) unsigned NOT NULL default '0',
  `d_cnt` int(11) unsigned NOT NULL default '0',
  `count_comment` int(10) unsigned NOT NULL default '0',
  `lastdate_comment` int(10) default '0' COMMENT '최종댓글등록일',
  `chkview` enum('Y','N','D') NOT NULL default 'Y',
  `chkcopy` enum('Y','N') NOT NULL default 'N',
  `chkhash` enum('Y','N') NOT NULL default 'Y',
  `size` bigint(20) unsigned NOT NULL default '0',
  `realsize` bigint(20) unsigned NOT NULL default '0',
  `uploader_grade` enum('1','2','3','4','5') NOT NULL default '5',
  `flag_notice` enum('0','1') NOT NULL default '0',
  `flag_passwd` enum('0','1') NOT NULL default '0',
  `flag_profile` enum('0','1') NOT NULL default '0',
  `flag_search` enum('0','1') NOT NULL default '0',
  `adult_chk` enum('Y','N') NOT NULL default 'N',
  `baby_chk` tinyint(1) NOT NULL default '0' COMMENT '1:유아',
  `mureka_chk` enum('Y','N') NOT NULL default 'Y',
  `regdate` int(10) unsigned NOT NULL default '0',
  `edit_date` int(11) NOT NULL,
  `chkblind` smallint(1) unsigned NOT NULL default '0',
  `chkiphone` smallint(1) unsigned NOT NULL default '0',
  `upload_site` int(11) NOT NULL COMMENT '업로드사이트 index',
  `upload_bbs_idx` int(11) NOT NULL COMMENT '업로드된 사이트 컨텐츠 번호',
  `upload_link` varchar(40) NOT NULL COMMENT '연동사이트 판매여부',
  `point_s` int(10) unsigned NOT NULL default '0',
  `d_cnt_s` int(10) unsigned NOT NULL default '0',
  `high` char(2) NOT NULL default 'N',
  `mobile_view` tinyint(1) NOT NULL default '0',
  `mobile_image` tinyint(1) NOT NULL default '0',
  `chkkid` tinyint(1) NOT NULL default '0',
  `m_photo_auto` char(2) NOT NULL default 'N',
  `m_photo_user` char(2) NOT NULL,
  `m_photo_extract` int(11) unsigned default '0',
  `m_size` bigint(20) unsigned default '0',
  `m_high` tinyint(1) unsigned NOT NULL default '0',
  `is_mobile` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `code_cate1` (`code_cate1`),
  KEY `code_cate2` (`code_cate2`),
  KEY `code` (`code`),
  KEY `flag_search` (`flag_search`),
  KEY `chkview` (`chkview`),
  KEY `pos` (`pos`),
  KEY `name` (`name`),
  KEY `realsize` (`realsize`),
  KEY `chkcopy` (`chkcopy`),
  KEY `size` (`size`),
  KEY `order_code` (`code`,`chkview`,`pos`),
  KEY `order_cate` (`code_cate2`,`chkview`,`pos`),
  KEY `chkblind` (`chkblind`),
  KEY `upload_site` (`upload_site`),
  KEY `upload_bbs_idx` (`upload_bbs_idx`),
  KEY `high` (`high`),
  KEY `point_s` (`point_s`),
  KEY `code_cate3` (`code_cate3`),
  KEY `code_cate4` (`code_cate4`),
  KEY `d_cnt_s` (`d_cnt_s`),
  KEY `mobile_view` (`mobile_view`),
  KEY `mobile_image` (`mobile_image`),
  KEY `baby_chk` (`baby_chk`),
  KEY `chkkid` (`chkkid`),
  KEY `m_photo_auto` (`m_photo_auto`),
  KEY `m_photo_user` (`m_photo_user`),
  KEY `chkiphone` (`chkiphone`,`m_photo_auto`),
  KEY `order_cate_adult` (`code_cate2`,`adult_chk`,`chkview`,`pos`),
  KEY `userid` (`userid`,`code_cate2`,`chkview`,`pos`),
  KEY `userid_contents` (`userid`,`chkview`,`pos`),
  KEY `adult_chk` (`adult_chk`,`chkview`,`pos`),
  KEY `regdate` (`regdate`),
  CONSTRAINT `_board_ibfk_1` FOREIGN KEY (`code`) REFERENCES `_board_control` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='게시판';
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:39
