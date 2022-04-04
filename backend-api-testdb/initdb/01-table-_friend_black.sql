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

/*Table structure for table `_friend_black` */

CREATE TABLE `_friend_black` (
  `idx` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `target_name` varchar(100) NOT NULL COMMENT '대상자',
  `memo` varchar(100) NOT NULL COMMENT '메모',
  `state` varchar(10) NOT NULL COMMENT '차단여부(차단:Y)',
  `regdate` varchar(20) NOT NULL,
  PRIMARY KEY  (`idx`),
  KEY `name` (`name`),
  KEY `target_name` (`target_name`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='내가차단한친구';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;