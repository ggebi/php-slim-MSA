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
-- Table structure for table `mmsv_webhard_pay`
--

CREATE TABLE `mmsv_webhard_pay` (
  `userid` varchar(20) NOT NULL default '',
  `packet` double(15,2) NOT NULL default '0.00',
  `reward` double(15,2) NOT NULL default '0.00',
  `item` double(15,2) NOT NULL default '0.00',
  `point` double(15,2) default '0.00',
  `keeping` double(15,2) default '0.00',
  `recom` double(15,2) default '0.00',
  `mileage` int(11) NOT NULL default '0' COMMENT '파일시티마일리지',
  `coupon` int(11) NOT NULL default '0',
  `thiat` int(11) NOT NULL default '0',
  `reward_bonus` tinyint(3) unsigned default '20',
  `use_halfmonth` bigint(20) unsigned NOT NULL default '0',
  `pre_halfmonth` bigint(20) unsigned NOT NULL default '0',
  `disk_space` int(10) unsigned NOT NULL default '0',
  `fix_start` int(10) unsigned NOT NULL default '0',
  `fix_end` int(10) unsigned NOT NULL default '0',
  `fix_sub_end` int(10) unsigned NOT NULL default '0',
  `fix_mode` enum('H','F') NOT NULL default 'F',
  `fix_time` enum('S','D','N','M','M2','M3') NOT NULL default 'S',
  `time_start` int(10) unsigned NOT NULL default '24',
  `time_end` int(10) unsigned NOT NULL default '24',
  `auto` enum('Y','N') NOT NULL default 'N',
  `auto_cancel` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`userid`),
  KEY `keeping` (`keeping`),
  KEY `use_halfmonth` (`use_halfmonth`),
  KEY `auto_ration` (`auto`,`auto_cancel`),
  KEY `mileage` (`mileage`),
  KEY `coupon` (`coupon`),
  KEY `reward` (`reward`)
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
