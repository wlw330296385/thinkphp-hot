/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : hot

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2017-12-18 18:17:24
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `grade_category`
-- ----------------------------
DROP TABLE IF EXISTS `grade_category`;
CREATE TABLE `grade_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '课程分类名',
  `url` varchar(255) NOT NULL COMMENT '图片地址',
  `sort` tinyint(2) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL COMMENT '状态:1正常|-1禁用|0默认',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '父级id',
  `delete_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=152 DEFAULT CHARSET=utf8 COMMENT='课程分类';

-- ----------------------------
-- Records of grade_category
-- ----------------------------
INSERT INTO `grade_category` VALUES ('54', '幼儿', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('55', '小学低年级', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('56', '小学高年级', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('57', '初中', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('58', '高中', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('59', '大学', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('60', '成人', '', '98', '0', '0', '0', '0', null);
INSERT INTO `grade_category` VALUES ('61', '篮球兴趣班', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('62', '基础篮球课程', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('63', '综合篮球课程', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('64', '强化篮球课程', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('65', '篮球队课程', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('66', '特色训练课程', '', '98', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('67', '其他', '', '100', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('68', '篮球兴趣班', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('69', '基础篮球课程', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('70', '综合篮球课程', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('71', '强化篮球课程', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('72', '篮球队课程', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('73', '特色训练课程', '', '98', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('74', '其他', '', '100', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('75', '篮球兴趣班', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('76', '基础篮球课程', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('77', '综合篮球课程', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('78', '强化篮球课程', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('79', '篮球队课程', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('80', '特色训练课程', '', '98', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('81', '其他', '', '100', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('82', '篮球兴趣班', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('83', '基础篮球课程', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('84', '综合篮球课程', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('85', '强化篮球课程', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('86', '篮球队课程', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('87', '特色训练课程', '', '98', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('88', '其他', '', '100', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('89', '篮球兴趣班', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('90', '基础篮球课程', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('91', '综合篮球课程', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('92', '强化篮球课程', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('93', '篮球队课程', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('94', '特色训练课程', '', '98', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('95', '其他', '', '100', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('96', '篮球兴趣班', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('97', '基础篮球课程', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('98', '综合篮球课程', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('99', '强化篮球课程', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('100', '篮球队课程', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('101', '特色训练课程', '', '98', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('102', '其他', '', '100', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('103', '篮球兴趣班', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('104', '基础篮球课程', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('105', '综合篮球课程', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('106', '强化篮球课程', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('107', '篮球队课程', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('108', '特色训练课程', '', '98', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('109', '其他', '', '100', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('110', '私教', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('111', '课外活动', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('112', '校园兴趣班', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('113', '企业（事业单位）', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('114', '私教', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('115', '课外活动', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('116', '校园兴趣班', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('117', '企业（事业单位）', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('118', '私教', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('119', '课外活动', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('120', '校园兴趣班', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('121', '企业（事业单位）', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('122', '私教', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('123', '课外活动', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('124', '校园兴趣班', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('125', '企业（事业单位）', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('126', '私教', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('127', '课外活动', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('128', '校园兴趣班', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('129', '企业（事业单位）', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('130', '私教', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('131', '课外活动', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('132', '校园兴趣班', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('133', '企业（事业单位）', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('134', '私教', '', '99', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('135', '课外活动', '', '99', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('136', '校园兴趣班', '', '99', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('137', '企业（事业单位）', '', '99', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('138', '花式篮球课程', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('139', '专项训练课程', '', '99', '0', '0', '0', '60', null);
INSERT INTO `grade_category` VALUES ('140', '花式篮球课程', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('141', '专项训练课程', '', '99', '0', '0', '0', '59', null);
INSERT INTO `grade_category` VALUES ('142', '花式篮球课程', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('143', '专项训练课程', '', '99', '0', '0', '0', '58', null);
INSERT INTO `grade_category` VALUES ('144', '花式篮球课程', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('145', '专项训练课程', '', '99', '0', '0', '0', '57', null);
INSERT INTO `grade_category` VALUES ('146', '花式篮球课程', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('147', '专项训练课程', '', '99', '0', '0', '0', '56', null);
INSERT INTO `grade_category` VALUES ('148', '花式篮球课程', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('149', '专项训练课程', '', '99', '0', '0', '0', '55', null);
INSERT INTO `grade_category` VALUES ('150', '花式篮球课程', '', '99', '0', '0', '0', '54', null);
INSERT INTO `grade_category` VALUES ('151', '专项训练课程', '', '99', '0', '0', '0', '54', null);
