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

/*Table structure for table `_account` */

CREATE TABLE `_account` (
  `idx` bigint(20) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL default '',
  `method` varchar(2) NOT NULL default 'P',
  `billtype` enum('P','F','A') NOT NULL default 'P',
  `cash` int(10) unsigned NOT NULL default '0',
  `realpay` int(10) NOT NULL COMMENT '실제결제금액(VAT제외)',
  `info` varchar(100) NOT NULL default '',
  `ip` varchar(20) NOT NULL default '',
  `regdate` int(10) unsigned NOT NULL default '0',
  `refer` varchar(255) NOT NULL,
  `state` tinyint(1) default '0' COMMENT '상태',
  `phone` varchar(12) default NULL COMMENT '전화번호',
  `paycode` varchar(40) default NULL COMMENT '결제코드',
  `calc_expect` date NOT NULL COMMENT '정산예정일',
  `calc_chrge` int(10) NOT NULL COMMENT '결제사수수료',
  `partner` varchar(20) default NULL COMMENT '파트너,추천인 아이디',
  `joinpath` enum('E','R','P','J','EC','RC','PC','JC') NOT NULL default 'E' COMMENT 'E-일반, R-추천인, P-파트너,J-여분,EC-일반(쿠폰), RC-추천인(쿠폰), PC-파트너(쿠폰),JC-여분',
  `pay_info_idx` smallint(4) NOT NULL COMMENT '결제상품고유값',
  `pay_method_idx` smallint(4) NOT NULL COMMENT '결제수단고유값',
  `pg_id` tinyint(2) unsigned NOT NULL COMMENT '결제사 ID',
  `chk_sta` tinyint(1) NOT NULL default '0',
  `chk_iphone` tinyint(1) default '0',
  PRIMARY KEY  (`idx`),
  KEY `cash` (`userid`,`method`,`billtype`,`cash`,`regdate`),
  KEY `regdate` (`regdate`),
  KEY `userid` (`userid`),
  KEY `phone` (`phone`),
  KEY `paycode` (`paycode`),
  KEY `joinpath` (`joinpath`),
  KEY `calc_expect` (`calc_expect`),
  KEY `realpay` (`realpay`),
  KEY `ip` (`ip`),
  KEY `chk_sta` (`chk_sta`),
  KEY `chk_iphone` (`chk_iphone`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO _account SET userid = 'jhwmon', cash = 5000, realpay = 5000, regdate = UNIX_TIMESTAMP(), refer = 'http://www.yesfile.com/', calc_expect = '2018-01-01', calc_chrge = 440, pay_info_idx = 76, pay_method_idx = 0, pg_id = 0;