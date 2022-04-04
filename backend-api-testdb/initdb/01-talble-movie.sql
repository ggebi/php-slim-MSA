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

/*Table structure for table `movie` */

CREATE TABLE `movie` (
  `idx` mediumint(8) unsigned NOT NULL auto_increment COMMENT '고유번호',
  `status` enum('Y','N') NOT NULL default 'Y' COMMENT '노출 상태',
  `cate1` varchar(3) NOT NULL default 'ALL' COMMENT '대분류',
  `cate2` varchar(3) NOT NULL default 'ALL' COMMENT '소분류',
  `sorting` mediumint(9) NOT NULL default '0' COMMENT '정렬',
  `represent_idx` int(1) unsigned NOT NULL default '0' COMMENT '대표 컨텐츠 idx',
  `is_adult` enum('Y','N') NOT NULL default 'N' COMMENT '성인여부',
  `is_hot` enum('Y','N') NOT NULL default 'N' COMMENT '인기여부',
  `pub_date` date NOT NULL COMMENT '개봉일',
  `rating` float(4,2) NOT NULL default '0.00' COMMENT '평점',
  `runtime` smallint(5) unsigned NOT NULL default '0' COMMENT '상영시간',
  `title` varchar(255) NOT NULL COMMENT '제목',
  `subtitle` varchar(255) NOT NULL COMMENT '부제목',
  `keyword` varchar(255) NOT NULL COMMENT '키워드',
  `thumbnail` varchar(128) NOT NULL COMMENT '포스터 썸네일',
  `director` varchar(128) NOT NULL COMMENT '감독',
  `actor` varchar(255) NOT NULL COMMENT '배우',
  `regdate` datetime NOT NULL COMMENT '등록일',
  `is_search` tinyint(1) NOT NULL default '0' COMMENT '1:검색결과있음, 2:검색결과없음',
  PRIMARY KEY  (`idx`),
  KEY `cate1` (`cate1`,`cate2`),
  KEY `pub_date` (`pub_date`),
  KEY `represent_idx` (`represent_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr COMMENT='최신영화콘텐츠';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
