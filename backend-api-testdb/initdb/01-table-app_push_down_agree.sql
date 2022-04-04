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

/* Table structure for table `app_push_down_agree` */

CREATE TABLE `app_push_down_agree` (
  `idx` int(1) unsigned NOT NULL auto_increment,
  `userid` varchar(40) NOT NULL COMMENT '회원ID',
  `agree_event` tinyint(1) unsigned NOT NULL default '0' COMMENT '광고푸시 수신동의여부(0:미동의, 1:동의, 2:취소)',
  `agree_private` tinyint(1) unsigned NOT NULL default '0' COMMENT '개인푸시 수신동의여부(0:미동의, 1:동의, 2:취소)',
  `date_agree_event` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '최근변경일시',
  `date_agree_private` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '최근변경일시',
  `date_live` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '최근앱실행일시',
  `regdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '생성일시',
  PRIMARY KEY (`idx`),
  UNIQUE KEY `userid` (`userid`),
  KEY `date_live` (`date_live`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='다운로드 앱 푸시 수신동의 관리 테이블';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;