/*
SQLyog Community v12.5.0 (32 bit)
MySQL - 5.0.33-log : Database - webhard
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`webhard` /*!40100 DEFAULT CHARACTER SET euckr */;

/*Table structure for table `_vod_contents_bbs_list` */

CREATE TABLE `_vod_contents_bbs_list` (
  `idx` int(11) NOT NULL auto_increment,
  `cidx` int(11) default NULL,
  `sidx` int(11) default NULL,
  `bbs_idx` int(11) default NULL,
  `regdate` int(11) default '0',
  PRIMARY KEY  (`idx`),
  KEY `sidx` (`sidx`),
  KEY `cidx` (`cidx`),
  KEY `bbs_idx` (`bbs_idx`),
  KEY `regdate` (`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
