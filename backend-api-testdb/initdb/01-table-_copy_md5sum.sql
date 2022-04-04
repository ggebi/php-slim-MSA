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
-- Table structure for table `_copy_md5sum`
--

CREATE TABLE `_copy_md5sum` (
  `idx` int(11) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL default '',
  `md5sum` varchar(32) NOT NULL default '',
  `set_point` smallint(5) unsigned NOT NULL default '0',
  `summary` varchar(255) NOT NULL,
  `inning` varchar(20) NOT NULL COMMENT '회차',
  `contents_id` varchar(30) NOT NULL default '',
  `use_chk` enum('Y','N') NOT NULL default 'Y',
  `hash_chk` enum('Y','N') NOT NULL default 'N',
  `opendate` date NOT NULL default '0000-00-00' COMMENT '방영일자',
  `regdate` int(10) unsigned NOT NULL default '0',
  `last_chkfileno` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  UNIQUE KEY `md5sum` (`md5sum`),
  KEY `userid` (`userid`),
  KEY `hash_chk` (`hash_chk`),
  KEY `inning` (`inning`),
  KEY `opendate` (`opendate`),
  KEY `contents_id` (`contents_id`)
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
