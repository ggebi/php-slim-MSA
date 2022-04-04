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

use `webhard`;

/*Table structure for table `_vod_contents_series` */

CREATE TABLE `_vod_contents_series` (
  `sidx` int(11) NOT NULL auto_increment,
  `cidx` int(11) default NULL,
  `inning` mediumint(9) default NULL,
  `inning_str` varchar(30) NOT NULL,
  `contents_id` varchar(50) default NULL,
  `list_update` int(11) default '0',
  `update_type` tinyint(1) default '0',
  `title_img` varchar(250) default '',
  `info` text,
  `opendate` date default '0000-00-00',
  `regdate` int(11) default NULL,
  PRIMARY KEY  (`sidx`),
  KEY `cidx` (`cidx`),
  KEY `inning` (`inning`),
  KEY `cidx_inning` (`cidx`,`inning`),
  KEY `use_ready` (`list_update`),
  KEY `update_type` (`update_type`),
  KEY `contents_id` (`contents_id`),
  KEY `opendate` (`opendate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
