/*
SQLyog Community v12.5.0 (32 bit)
MySQL - 5.0.33-log : Database - webhard
*********************************************************************
*/

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`webhard` /*!40100 DEFAULT CHARACTER SET euckr */;

use `webhard`;

/*Table structure for table `_board_bookmark` */

CREATE TABLE `_board_bookmark` (
  `idx` int(11) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL,
  `bbs_no` int(10) unsigned NOT NULL default '0',
  `title` varchar(200) NOT NULL default '',
  `size` bigint(20) NOT NULL default '0',
  `code` varchar(12) NOT NULL default '',
  `uploader` varchar(20) NOT NULL default '',
  `link_mobile` tinyint(1) NOT NULL default '0' COMMENT '1:모바일찜사용',
  `chk_iphone` tinyint(1) NOT NULL default '0' COMMENT '1:모바일에서추가',
  `regdate` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`idx`),
  KEY `_bbsno` (`bbs_no`),
  KEY `unique_check` (`userid`,`bbs_no`),
  KEY `link_mobile` (`link_mobile`),
  KEY `chk_iphone` (`chk_iphone`)
  -- CONSTRAINT `_board_bookmark_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `_member` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE,
  -- CONSTRAINT `_board_bookmark_ibfk_2` FOREIGN KEY (`bbs_no`) REFERENCES `_board` (`idx`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;