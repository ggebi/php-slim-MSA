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
-- Table structure for table `_board_comment`
--

DROP TABLE IF EXISTS `_board_comment`;
CREATE TABLE `_board_comment` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `state` tinyint(1) NOT NULL default '0',
  `link_idx` int(10) unsigned NOT NULL default '0',
  `seller_userid` varchar(20) default NULL,
  `userid` varchar(20) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  `level` tinyint(3) unsigned NOT NULL default '0',
  `comment` mediumtext NOT NULL,
  `ip` varchar(20) NOT NULL default '',
  `thread` int(10) unsigned NOT NULL default '0',
  `pos` decimal(10,4) NOT NULL default '0.0000',
  `depth` tinyint(3) unsigned NOT NULL default '0',
  `point` varchar(100) NOT NULL default '',
  `regdate` int(10) unsigned NOT NULL default '0',
  `reply` tinyint(1) NOT NULL COMMENT '답변여부',
  `grade` tinyint(2) NOT NULL default '0' COMMENT '평가(0:좋아요,1:별로예요)',
  `gift` tinyint(1) unsigned NOT NULL default '0',
  `is_mobile` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `userid` (`userid`),
  KEY `orderpos` (`link_idx`,`pos`),
  KEY `link_idx` (`link_idx`),
  KEY `reply` (`reply`),
  KEY `state` (`state`),
  KEY `grade` (`grade`),
  KEY `gift` (`gift`),
  KEY `thread` (`thread`),
  KEY `pos` (`pos`),
  KEY `depth` (`depth`),
  KEY `seller_userid` (`seller_userid`,`depth`,`reply`,`regdate`),
  KEY `is_mobile` (`is_mobile`),
  CONSTRAINT `_board_comment_ibfk_1` FOREIGN KEY (`link_idx`) REFERENCES `_board` (`idx`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=euckr;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:38
