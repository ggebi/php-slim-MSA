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
-- Table structure for table `_copy_contents`
--

CREATE TABLE `_copy_contents` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `state` tinyint(1) unsigned NOT NULL default '0' COMMENT '0:기본, 1:전체차단, 2:모바일차단',
  `cate` varchar(20) NOT NULL default '',
  `copyid` varchar(20) NOT NULL default '',
  `copyname` varchar(100) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `inning` varchar(20) NOT NULL COMMENT '회차',
  `set_point` int(10) unsigned NOT NULL default '0',
  `contents_id` varchar(30) NOT NULL default '',
  `averg` int(2) unsigned NOT NULL default '0',
  `etc_info` varchar(255) default NULL,
  `opendate` date NOT NULL default '0000-00-00' COMMENT '시작일,방영일',
  `regdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  UNIQUE KEY `contents_id` (`contents_id`),
  KEY `cate` (`cate`),
  KEY `copyid` (`copyid`),
  KEY `inning` (`inning`),
  KEY `startday` (`opendate`),
  KEY `state` (`state`,`copyid`)
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
