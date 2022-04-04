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
-- Table structure for table `ssomon_hash`
--

DROP TABLE IF EXISTS `ssomon_hash`;
CREATE TABLE `ssomon_hash` (
  `idx` bigint(20) unsigned NOT NULL auto_increment,
  `copyid` varchar(20) NOT NULL,
  `type` char(2) NOT NULL,
  `fileidx` bigint(20) unsigned NOT NULL default '0',
  `bbs_no` bigint(20) unsigned NOT NULL default '0',
  `mureka_hash` varbinary(120) NOT NULL default '',
  `md5sum` varchar(32) NOT NULL default '',
  `video_id` varchar(50) NOT NULL,
  `video_title` varchar(200) NOT NULL,
  `video_jejak_year` varchar(4) NOT NULL,
  `video_right_name` varchar(200) NOT NULL,
  `video_right_content_id` varchar(50) NOT NULL,
  `video_price` varchar(10) NOT NULL,
  `video_cha` varchar(10) NOT NULL,
  `video_osp_jibun` int(10) unsigned NOT NULL,
  `video_onair_date` varchar(10) NOT NULL,
  `video_right_id` varchar(20) NOT NULL,
  `regdate` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`idx`),
  KEY `type` (`type`),
  KEY `mureka_hash` (`mureka_hash`),
  KEY `md5sum` (`md5sum`),
  KEY `video_right_content_id` (`video_right_content_id`),
  KEY `copyid` (`copyid`),
  KEY `bbs_no` (`bbs_no`),
  KEY `fileidx` (`fileidx`)
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
