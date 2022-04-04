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

/* Table structure for table `mmsv_webhard_file_resolution` */

CREATE TABLE `mmsv_webhard_file_resolution` (
  `no` bigint(11) unsigned NOT NULL,
  `resolution_w` int(11) NOT NULL,
  `resolution_h` int(11) NOT NULL,
  `codec` varchar(50) NOT NULL COMMENT '코덱',
  `frame` double(15,2) unsigned NOT NULL COMMENT '초당프레임',
  `play_time` int(11) unsigned NOT NULL COMMENT '재생시간',
  `screen_ratio` varchar(10) NOT NULL COMMENT '비율',
  `picture_info1` varchar(50) NOT NULL COMMENT '영상정보',
  UNIQUE KEY `no` (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;