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

/*Table structure for table `_seller_gain` */

CREATE TABLE `_seller_gain` (
  `idx` int(11) NOT NULL auto_increment,
  `state` tinyint(3) unsigned NOT NULL default '0' COMMENT '상태 0:정상, 1:삭제',
  `userid` varchar(20) NOT NULL,
  `copyid` varchar(20) NOT NULL default '',
  `contents_id` varchar(30) NOT NULL default '',
  `summary` varchar(255) NOT NULL,
  `recv_userid` varchar(20) NOT NULL,
  `recv_nickname` varchar(20) NOT NULL default '',
  `bbs_no` int(10) NOT NULL,
  `title` varchar(200) NOT NULL,
  `down_mode` enum('BBS_P','BBS_B','BBS_F','BBS_C','BBS_S','EXP_P','EXP_F','BBS_CP') NOT NULL default 'BBS_P',
  `packet_use` double(15,2) NOT NULL default '0.00',
  `packet_save` double(15,2) NOT NULL default '0.00',
  `copy_idx` int(11) unsigned NOT NULL default '0',
  `packet_have` double(15,2) NOT NULL default '0.00',
  `point_have` double(15,2) NOT NULL default '0.00',
  `coupon_have` int(11) NOT NULL default '0',
  `chk_iphone` tinyint(1) NOT NULL default '0' COMMENT '1:모바일에서구매',
  `regdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `userid` (`userid`),
  KEY `rec_userid` (`recv_userid`),
  KEY `copyid` (`copyid`),
  KEY `order_pos` (`userid`,`regdate`),
  KEY `down_mode` (`down_mode`),
  KEY `regdate` (`regdate`),
  KEY `state` (`state`),
  KEY `bbs_no` (`bbs_no`),
  KEY `chk_iphone` (`chk_iphone`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='판매캐쉬 적립';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;