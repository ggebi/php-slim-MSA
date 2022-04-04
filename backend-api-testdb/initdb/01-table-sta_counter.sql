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

/*Table structure for table `sta_counter` */

CREATE TABLE `sta_counter` (
  `idx` int(11) unsigned NOT NULL auto_increment,
  `code` varchar(40) NOT NULL,
  `kind` tinyint(1) unsigned NOT NULL,
  `sta_date` date NOT NULL,
  `total` int(11) unsigned NOT NULL,
  `h0` int(11) unsigned NOT NULL,
  `h1` int(11) unsigned NOT NULL,
  `h2` int(11) unsigned NOT NULL,
  `h3` int(11) unsigned NOT NULL,
  `h4` int(11) unsigned NOT NULL,
  `h5` int(11) unsigned NOT NULL,
  `h6` int(11) unsigned NOT NULL,
  `h7` int(11) unsigned NOT NULL,
  `h8` int(11) unsigned NOT NULL,
  `h9` int(11) unsigned NOT NULL,
  `h10` int(11) unsigned NOT NULL,
  `h11` int(11) unsigned NOT NULL,
  `h12` int(11) unsigned NOT NULL,
  `h13` int(11) unsigned NOT NULL,
  `h14` int(11) unsigned NOT NULL,
  `h15` int(11) unsigned NOT NULL,
  `h16` int(11) unsigned NOT NULL,
  `h17` int(11) unsigned NOT NULL,
  `h18` int(11) unsigned NOT NULL,
  `h19` int(11) unsigned NOT NULL,
  `h20` int(11) unsigned NOT NULL,
  `h21` int(11) unsigned NOT NULL,
  `h22` int(11) unsigned NOT NULL,
  `h23` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`idx`),
  UNIQUE KEY `code` (`code`,`kind`,`sta_date`)
) ENGINE=InnoDB DEFAULT CHARSET=euckr;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
