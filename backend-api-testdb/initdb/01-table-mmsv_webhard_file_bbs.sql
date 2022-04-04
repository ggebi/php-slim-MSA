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
-- Table structure for table `mmsv_webhard_file_bbs`
--

DROP TABLE IF EXISTS `mmsv_webhard_file_bbs`;
CREATE TABLE `mmsv_webhard_file_bbs` (
  `no` bigint(20) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL default '',
  `bbs_no` bigint(20) unsigned NOT NULL default '0',
  `idx` smallint(6) unsigned NOT NULL default '0',
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `foldername` varchar(250) NOT NULL default '',
  `realname` varchar(250) NOT NULL default '',
  `size` bigint(20) unsigned NOT NULL default '0',
  `realsize` bigint(20) unsigned NOT NULL default '0',
  `count_fix` int(11) unsigned NOT NULL default '0',
  `count_packet` int(11) unsigned NOT NULL default '0',
  `md5sum` varchar(32) NOT NULL default '',
  `upload_date` int(10) unsigned NOT NULL default '0',
  `flag_upload` enum('Y','N') NOT NULL default 'N',
  `flag_realupload` enum('Y','N') NOT NULL default 'N',
  `flag_warn` enum('Y','N') NOT NULL default 'N',
  `temp_volume` smallint(5) unsigned NOT NULL default '0',
  `temp_name` varchar(255) NOT NULL default '',
  `flag_exist` enum('Y','N') NOT NULL default 'N' COMMENT 'Y-damage, N-OK',
  `encoding` smallint(3) NOT NULL COMMENT '0:원본,1:원본이아이폰등록된경우,2:아이폰파일',
  PRIMARY KEY  (`no`),
  KEY `realsize` (`realsize`),
  KEY `size` (`size`,`realsize`),
  KEY `temp_volume` (`temp_volume`),
  KEY `md5sum` (`md5sum`),
  KEY `bbs_no_idx` (`bbs_no`,`no`),
  KEY `temp_name` (`temp_name`),
  KEY `userid` (`userid`),
  KEY `upload_date` (`upload_date`),
  KEY `bbs_no` (`bbs_no`),
  KEY `size_sum` (`realsize`),
  KEY `encoding` (`encoding`),
  KEY `realname` (`realname`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:41
