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
-- Table structure for table `encode_complete`
--

DROP TABLE IF EXISTS `encode_complete`;
CREATE TABLE `encode_complete` (
  `idx` int(10) NOT NULL auto_increment,
  `org_md5sum` varchar(32) NOT NULL COMMENT '원본md5sum',
  `org_file_idx` int(10) unsigned default NULL COMMENT '원본file_idx',
  `enc_md5sum` varchar(32) NOT NULL,
  `userid` varchar(50) default NULL,
  `bbs_no` int(10) default NULL,
  `uidx` smallint(6) default NULL,
  `depth` tinyint(3) default NULL,
  `foldername` varchar(250) default NULL COMMENT '사용자 다운로드시 생성폴더명',
  `realname` varchar(250) default NULL,
  `size` bigint(20) default NULL,
  `realupload` enum('Y','N') default NULL,
  `temp_volume` smallint(5) default NULL COMMENT '인코딩 업로드 서버',
  `ori_volume` smallint(5) default NULL COMMENT '원본 서버',
  `volume_volume` varchar(20) NOT NULL,
  `temp_name` varchar(255) default NULL,
  `regdate` int(10) default NULL,
  `regdate2` datetime default NULL COMMENT '날짜2',
  `etkey` int(10) NOT NULL default '0' COMMENT '방송자료키값',
  `m_high` tinyint(1) unsigned NOT NULL default '0' COMMENT '고화질:1',
  `contents_id` varchar(100) NOT NULL,
  `clip` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `org_md5sum` (`org_md5sum`),
  KEY `size` (`size`),
  KEY `depth` (`depth`,`foldername`,`realname`),
  KEY `bbs_no` (`bbs_no`),
  KEY `temp_volume` (`temp_volume`),
  KEY `temp_name` (`temp_name`),
  KEY `org_file_idx` (`org_file_idx`),
  KEY `contents_id` (`contents_id`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='인코딩 완료';
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:40
