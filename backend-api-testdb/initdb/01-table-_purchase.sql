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

/*Table structure for table `_purchase` */

CREATE TABLE `_purchase` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `bbs_no` int(10) unsigned NOT NULL default '0' COMMENT '컨텐츠 번호',
  `credit_check` tinyint(3) unsigned NOT NULL default '0' COMMENT '0:구매,1:환불',
  `target` tinyint(3) NOT NULL default '0' COMMENT '0:사이트,1:탐색기,2:모바일웹,3:이벤트웹',
  `billtype` varchar(10) NOT NULL COMMENT 'J:제휴포인트,P:포인트,B:보너스,D:액,N:야간정액,S:스페셜정액,C:쿠폰',
  `point` int(10) NOT NULL default '0' COMMENT '보너스',
  `packet` int(10) NOT NULL default '0' COMMENT '패킷',
  `state` tinyint(3) unsigned NOT NULL default '0' COMMENT '0:정상, 1:삭제',
  `title` varchar(200) NOT NULL COMMENT '제목',
  `code` varchar(10) NOT NULL COMMENT '카테고리 코드',
  `size` bigint(20) NOT NULL default '0' COMMENT '용량',
  `grade` tinyint(3) unsigned NOT NULL default '0' COMMENT '등급',
  `userid` varchar(20) NOT NULL default '' COMMENT '사용자ID',
  `usernick` varchar(20) NOT NULL COMMENT '사용자닉네임',
  `userid_seller` varchar(20) NOT NULL COMMENT '판매자ID',
  `seller_nick` varchar(20) NOT NULL COMMENT '판매자닉네임',
  `expiredate` int(10) unsigned NOT NULL default '0' COMMENT '만료일자',
  `regdate` int(10) unsigned NOT NULL default '0' COMMENT '구매일자',
  `link_mobile` tinyint(1) NOT NULL default '0' COMMENT '1:모바일공통',
  `downlod_count` tinyint(1) unsigned NOT NULL default '0' COMMENT '다운로드횟수',
  `downlod_count_m` tinyint(1) unsigned NOT NULL default '0',
  `stream_count` tinyint(1) unsigned NOT NULL default '0',
  `copyid` varchar(40) default NULL COMMENT '제휴사ID',
  `ip` varchar(20) default NULL,
  PRIMARY KEY  (`idx`),
  KEY `userid` (`userid`),
  KEY `bbs_no` (`bbs_no`),
  KEY `expiredate` (`expiredate`),
  KEY `state` (`state`),
  KEY `billtype` (`billtype`),
  KEY `code` (`code`),
  KEY `userid_seller` (`userid_seller`),
  KEY `grade` (`grade`),
  KEY `seller_nick` (`seller_nick`),
  KEY `targe` (`target`),
  KEY `link_mobile` (`link_mobile`),
  KEY `regdate` (`regdate`,`code`,`bbs_no`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;