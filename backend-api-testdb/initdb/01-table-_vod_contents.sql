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

/*Table structure for table `_vod_contents` */

CREATE TABLE `_vod_contents` (
  `cidx` int(11) NOT NULL auto_increment,
  `state` tinyint(2) NOT NULL default '0',
  `genre` tinyint(2) NOT NULL,
  `onair` enum('Y','N') NOT NULL default 'Y',
  `title` varchar(100) NOT NULL,
  `season` tinyint(2) NOT NULL default '0',
  `contents_id_type` varchar(100) NOT NULL,
  `poster` varchar(150) NOT NULL,
  `poster_mobile` varchar(150) NOT NULL,
  `broadcaster` varchar(30) NOT NULL,
  `info` text NOT NULL,
  `actor` varchar(150) NOT NULL,
  `show_day` varchar(128) NOT NULL,
  `show_time` time NOT NULL,
  `show_age` tinyint(4) NOT NULL,
  `lastdate` date NOT NULL default '0000-00-00',
  `regdate` int(11) NOT NULL,
  PRIMARY KEY  (`cidx`),
  KEY `cate_1` (`genre`),
  KEY `contents_id_type` (`contents_id_type`),
  KEY `broadcaster` (`broadcaster`),
  KEY `state` (`state`),
  KEY `onair` (`onair`),
  KEY `poster` (`poster`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
