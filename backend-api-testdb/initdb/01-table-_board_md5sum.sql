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
-- Table structure for table `_board_md5sum`
--

DROP TABLE IF EXISTS `_board_md5sum`;
CREATE TABLE `_board_md5sum` (
  `idx` int(11) unsigned NOT NULL auto_increment,
  `copyid` varchar(20) NOT NULL default '',
  `set_point` smallint(5) unsigned NOT NULL default '0',
  `bbs_idx` int(11) unsigned NOT NULL default '0',
  `file_bbs_no` bigint(20) unsigned NOT NULL default '0',
  `use_chk` enum('Y','N') NOT NULL default 'Y',
  `down_chk` smallint(5) unsigned NOT NULL default '0',
  `copyno` int(11) unsigned NOT NULL default '0',
  `contents_id` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`idx`),
  UNIQUE KEY `file_bbs_no` (`file_bbs_no`),
  KEY `copyid` (`copyid`),
  KEY `copyno` (`copyno`),
  KEY `contents_id` (`contents_id`),
  KEY `bbs_idx` (`bbs_idx`),
  KEY `use_chk` (`use_chk`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:40
