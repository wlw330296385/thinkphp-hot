/*
 Navicat Premium Data Transfer

 Source Server         : 32
 Source Server Type    : MySQL
 Source Server Version : 100126
 Source Host           : 127.0.0.1:3306
 Source Schema         : hot

 Target Server Type    : MySQL
 Target Server Version : 100126
 File Encoding         : 65001

 Date: 04/04/2018 10:56:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for daily_camp_finance
-- ----------------------------
DROP TABLE IF EXISTS `daily_camp_finance`;
CREATE TABLE `daily_camp_finance`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `camp_id` int(11) NOT NULL,
  `camp` int(60) NOT NULL,
  `e_balance` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '结束余额',
  `s_balance` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '开始余额',
  `date_str` int(11) NOT NULL COMMENT '20180204',
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `delete_time` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 145 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of daily_camp_finance
-- ----------------------------
INSERT INTO `daily_camp_finance` VALUES (1, 37, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (2, 36, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (3, 34, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (4, 33, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (5, 32, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (6, 31, 0, 6.00, 6.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (7, 29, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (8, 19, 17, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (9, 17, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (10, 16, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (11, 15, 0, 52060.00, 52060.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (12, 14, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (13, 13, 0, 57726.80, 57726.80, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (14, 9, 0, 439546.00, 439546.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (15, 4, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (16, 3, 0, 0.00, 0.00, 20180327, 1522080007, 1522166346, 0);
INSERT INTO `daily_camp_finance` VALUES (17, 37, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (18, 36, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (19, 34, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (20, 33, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (21, 32, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (22, 31, 0, 6.00, 6.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (23, 29, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (24, 19, 17, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (25, 17, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (26, 16, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (27, 15, 0, 52060.00, 52060.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (28, 14, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (29, 13, 0, 57726.80, 57726.80, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (30, 9, 0, 439546.00, 439546.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (31, 4, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (32, 3, 0, 0.00, 0.00, 20180328, 1522166407, 1522252747, 0);
INSERT INTO `daily_camp_finance` VALUES (33, 37, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (34, 36, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (35, 34, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (36, 33, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (37, 32, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (38, 31, 0, 6.00, 6.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (39, 29, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (40, 19, 17, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (41, 17, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (42, 16, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (43, 15, 0, 52060.00, 52060.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (44, 14, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (45, 13, 0, 57726.80, 57726.80, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (46, 9, 0, 439546.00, 439546.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (47, 4, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (48, 3, 0, 0.00, 0.00, 20180329, 1522252807, 1522339147, 0);
INSERT INTO `daily_camp_finance` VALUES (49, 37, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (50, 36, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (51, 34, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (52, 33, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (53, 32, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (54, 31, 0, 3.00, 6.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (55, 29, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (56, 19, 17, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (57, 17, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (58, 16, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (59, 15, 0, 21110.00, 52060.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (60, 14, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (61, 13, 0, 28863.40, 57726.80, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (62, 9, 0, 219773.00, 439546.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (63, 4, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (64, 3, 0, 0.00, 0.00, 20180330, 1522339207, 1522425547, 0);
INSERT INTO `daily_camp_finance` VALUES (65, 37, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (66, 36, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (67, 34, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (68, 33, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (69, 32, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (70, 31, 0, 3.00, 3.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (71, 29, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (72, 19, 17, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (73, 17, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (74, 16, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (75, 15, 0, 21110.00, 21110.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (76, 14, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (77, 13, 0, 28863.40, 28863.40, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (78, 9, 0, 219773.00, 219773.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (79, 4, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (80, 3, 0, 0.00, 0.00, 20180331, 1522425607, 1522511946, 0);
INSERT INTO `daily_camp_finance` VALUES (81, 37, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (82, 36, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (83, 34, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (84, 33, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (85, 32, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (86, 31, 0, 3.00, 3.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (87, 29, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (88, 19, 17, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (89, 17, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (90, 16, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (91, 15, 0, 21110.00, 21110.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (92, 14, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (93, 13, 0, 28863.40, 28863.40, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (94, 9, 0, 219773.00, 219773.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (95, 4, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (96, 3, 0, 0.00, 0.00, 20180401, 1522512008, 1522598347, 0);
INSERT INTO `daily_camp_finance` VALUES (97, 37, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (98, 36, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (99, 34, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (100, 33, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (101, 32, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (102, 31, 0, 3.00, 3.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (103, 29, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (104, 19, 17, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (105, 17, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (106, 16, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (107, 15, 0, 27380.00, 21110.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (108, 14, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (109, 13, 0, 35578.50, 28863.40, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (110, 9, 0, 554773.00, 219773.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (111, 4, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (112, 3, 0, 0.00, 0.00, 20180402, 1522598407, 1522684747, 0);
INSERT INTO `daily_camp_finance` VALUES (113, 37, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (114, 36, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (115, 34, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (116, 33, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (117, 32, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (118, 31, 0, 3.00, 3.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (119, 29, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (120, 19, 17, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (121, 17, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (122, 16, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (123, 15, 0, 27380.00, 27380.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (124, 14, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (125, 13, 0, 35578.50, 35578.50, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (126, 9, 0, 554773.00, 554773.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (127, 4, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (128, 3, 0, 0.00, 0.00, 20180403, 1522684806, 1522771147, 0);
INSERT INTO `daily_camp_finance` VALUES (129, 37, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (130, 36, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (131, 34, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (132, 33, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (133, 32, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (134, 31, 0, 0.00, 3.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (135, 29, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (136, 19, 17, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (137, 17, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (138, 16, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (139, 15, 0, 0.00, 27380.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (140, 14, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (141, 13, 0, 0.00, 35578.50, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (142, 9, 0, 0.00, 554773.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (143, 4, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);
INSERT INTO `daily_camp_finance` VALUES (144, 3, 0, 0.00, 0.00, 20180404, 1522771212, 0, 0);

SET FOREIGN_KEY_CHECKS = 1;
