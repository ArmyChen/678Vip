/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50540
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50540
File Encoding         : 65001

Date: 2017-08-03 22:22:09
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tiz_root
-- ----------------------------
DROP TABLE IF EXISTS `tiz_root`;
CREATE TABLE `tiz_root` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `start_city` varchar(255) DEFAULT NULL,
  `start_city_code` varchar(255) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `nature_id` int(11) DEFAULT NULL,
  `nature_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `other_name` varchar(255) DEFAULT NULL,
  `root_code` varchar(255) DEFAULT NULL,
  `backway` varchar(255) DEFAULT NULL,
  `root_theme` varchar(255) DEFAULT NULL,
  `check_type` int(11) DEFAULT NULL,
  `description` text,
  `need_desc` text,
  `tip_desc` text,
  `fee_desc` text,
  `no_fee_desc` text,
  `safe_desc` text,
  `product_desc` text,
  `content_desc` text,
  `is_refund` tinyint(4) DEFAULT NULL,
  `detail_rule` int(11) DEFAULT NULL,
  `created` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tiz_root
-- ----------------------------

-- ----------------------------
-- Table structure for tiz_root_date
-- ----------------------------
DROP TABLE IF EXISTS `tiz_root_date`;
CREATE TABLE `tiz_root_date` (
  `id` int(11) NOT NULL,
  `tiz_id` int(11) DEFAULT NULL,
  `start_time` varchar(255) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `price` decimal(10,0) DEFAULT NULL,
  `msg` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tiz_root_date
-- ----------------------------
