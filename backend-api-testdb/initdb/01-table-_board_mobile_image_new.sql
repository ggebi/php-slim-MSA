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
-- Table structure for table `_board_mobile_image_new`
--

DROP TABLE IF EXISTS `_board_mobile_image_new`;
CREATE TABLE `_board_mobile_image_new` (
  `idx` int(10) unsigned NOT NULL auto_increment,
  `state` tinyint(1) unsigned NOT NULL COMMENT '1(정상),2(삭제대상),0(확인대기),9(업로드오류)',
  `kind` tinyint(2) unsigned NOT NULL default '0' COMMENT '용도구분(1:리스트용,2:플레이용,0:상세정보용,3:ipad용,4:상세용(자체추출),5:일반리스트용(자체추출),6:리스트용(아이패드)',
  `board_mobile_idx` int(10) unsigned NOT NULL,
  `pos` int(10) unsigned NOT NULL,
  `image_domain` varchar(100) NOT NULL,
  `image_dir` varchar(200) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `original_board_idx` int(11) NOT NULL,
  PRIMARY KEY  (`idx`),
  KEY `state` (`state`),
  KEY `board_mobile_idx` (`board_mobile_idx`,`pos`),
  KEY `image_domain` (`image_domain`),
  KEY `image_dir` (`image_dir`),
  KEY `kind` (`kind`),
  KEY `image_name` (`image_name`),
  KEY `original_board_idx` (`original_board_idx`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-07-05  1:46:40
