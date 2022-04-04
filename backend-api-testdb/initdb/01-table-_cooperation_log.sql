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

/*Table structure for table `_cooperation_log` */

CREATE TABLE `_cooperation_log` (
  `idx` int(11) NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL,
  `copyid` varchar(20) NOT NULL default '',
  `recv_userid` varchar(20) NOT NULL,
  `recv_nickname` varchar(20) NOT NULL default '',
  `cp_userid` varchar(20) NOT NULL default '',
  `bbs_no` int(10) NOT NULL,
  `title` varchar(200) NOT NULL,
  `down_mode` enum('BBS_CP','BBS_P','BBS_F','BBS_C','BBS_S') NOT NULL default 'BBS_CP',
  `contents_id` varchar(30) NOT NULL default '',
  `packet_use` double(15,2) NOT NULL default '0.00',
  `packet_save` double(15,2) NOT NULL default '0.00',
  `regdate` int(10) unsigned NOT NULL default '0',
  `cancel` enum('Y','N') NOT NULL default 'N',
  `cancel_date` int(10) unsigned NOT NULL default '0',
  `cancel_reason` varchar(255) default NULL,
  `cpr_div` varchar(35) NOT NULL default '',
  `chkiphone` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`idx`),
  KEY `userid` (`userid`),
  KEY `contents_id` (`contents_id`),
  KEY `copyid` (`copyid`),
  KEY `recv_userid` (`recv_userid`),
  KEY `title` (`title`),
  KEY `cp_userid` (`cp_userid`),
  KEY `bbs_no` (`bbs_no`),
  KEY `is_mobile` (`chkiphone`,`idx`),
  KEY `regdate_packet` (`regdate`,`packet_use`),
  KEY `cancel_regdate` (`cancel`,`regdate`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='저작권 제휴';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;