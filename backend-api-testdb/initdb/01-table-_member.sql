-- MySQL dump 10.11
--
-- Host: localhost    Database: webhard
-- ------------------------------------------------------
-- Server version	5.0.33-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

use `webhard`;

--
-- Table structure for table `_member`
--

CREATE TABLE `_member` (
  `idx` int(11) unsigned NOT NULL auto_increment,
  `userid` varchar(20) NOT NULL default '',
  `userpw` varchar(100) NOT NULL default '',
  `name` varchar(20) NOT NULL default '',
  `level` tinyint(3) unsigned NOT NULL default '9',
  `nickname` varchar(20) NOT NULL default '',
  `jumin` varchar(50) NOT NULL default '',
  `email` varchar(128) NOT NULL default '' COMMENT '이메일\n',
  `tel` varchar(20) NOT NULL default '',
  `hpcorp` varchar(3) NOT NULL default '' COMMENT '휴대폰 통신사',
  `hp` varchar(16) NOT NULL default '' COMMENT '휴대폰',
  `zipcode` varchar(8) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `recomid` varchar(20) default NULL,
  `mailing` enum('0','1') NOT NULL default '0',
  `auth` enum('0','1') NOT NULL default '0' COMMENT '실명인증 구분',
  `adult` enum('0','1','2','3') NOT NULL default '0' COMMENT '성인인증',
  `ip` varchar(30) NOT NULL default '',
  `ip_last` varchar(30) default NULL COMMENT '마지막접속',
  `connect` mediumint(8) unsigned NOT NULL default '0',
  `last_connect` int(10) unsigned NOT NULL default '0',
  `note` mediumtext NOT NULL COMMENT '회원관리정보',
  `cp` tinyint(3) unsigned NOT NULL default '0',
  `bandate` int(10) unsigned NOT NULL default '0' COMMENT '회원탈퇴처리일',
  `control` tinyint(3) unsigned NOT NULL default '0' COMMENT '회원상태표시',
  `pg_id` varchar(15) default NULL COMMENT '결제사 ID',
  `flag_admin` enum('0','1') NOT NULL default '0',
  `regdate` int(10) unsigned NOT NULL default '0',
  `join_site` int(1) NOT NULL COMMENT '1파일시티 2예스파일',
  `chk_iphone` tinyint(1) unsigned NOT NULL,
  `member_key` varchar(10) default NULL,
  `notice_date` int(10) unsigned NOT NULL COMMENT '공지확인일시',
  PRIMARY KEY  (`idx`),
  KEY `userid` (`userid`),
  KEY `name` (`name`),
  KEY `nickname` (`nickname`),
  KEY `jumin` (`jumin`),
  KEY `email` (`email`),
  KEY `regdate` (`regdate`),
  KEY `control` (`control`),
  KEY `ip` (`ip`),
  KEY `level` (`level`),
  KEY `ip_last` (`ip_last`),
  KEY `mailing` (`mailing`),
  KEY `chk_iphone` (`chk_iphone`),
  KEY `NewIndex1` (`join_site`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='회원정보 - 기본테이블';
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:40
