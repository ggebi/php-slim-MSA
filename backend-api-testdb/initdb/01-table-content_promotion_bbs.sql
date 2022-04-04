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

/*Table structure for table `content_promotion_bbs` */

CREATE TABLE `content_promotion_bbs` (
  `idx` int(1) unsigned NOT NULL auto_increment,
  `state` tinyint(1) unsigned NOT NULL default '1' COMMENT '0:誘몄쟻??1:?곸슜,2:醫낅즺',
  `list_idx` int(1) unsigned NOT NULL COMMENT 'content_promotion_list idx',
  `id_idx` int(1) unsigned NOT NULL COMMENT 'content_promotion_id idx',
  `bbs_idx` int(1) unsigned NOT NULL COMMENT '_webhard_bbs idx',
  `file_idx` int(1) unsigned NOT NULL default '0' COMMENT '_webhard_file_bbs no',
  `org_price` int(1) unsigned NOT NULL default '0' COMMENT '?좎씤??湲덉븸',
  `discount` tinyint(1) unsigned NOT NULL default '0' COMMENT '?좎씤瑜',
  `discount_kind` tinyint(1) unsigned NOT NULL default '1' COMMENT '1:?좎씤,2:臾대즺',
  `bonus_coupon` int(1) unsigned NOT NULL default '0' COMMENT '援щℓ??吏?툒荑좏룿?섎웾',
  `bonus_point` int(1) unsigned NOT NULL default '0' COMMENT '援щℓ??吏?툒?ъ씤?몄닔?',
  `bonus_packet` int(1) unsigned NOT NULL default '0',
  `icon` tinyint(1) unsigned NOT NULL default '0',
  `regdate` int(1) unsigned NOT NULL default '0' COMMENT '?앹꽦?쇱떆',
  PRIMARY KEY  (`idx`),
  UNIQUE KEY `file_idx` (`file_idx`),
  KEY `bbs_idx` (`bbs_idx`),
  KEY `list_idx` (`list_idx`),
  KEY `id_idx` (`id_idx`),
  KEY `state` (`state`),
  KEY `kind` (`discount_kind`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
