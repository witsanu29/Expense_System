/*
 Navicat Premium Data Transfer

 Source Server         : Notebook
 Source Server Type    : MySQL
 Source Server Version : 100017
 Source Host           : 192.168.100.138:3306
 Source Schema         : finance_db

 Target Server Type    : MySQL
 Target Server Version : 100017
 File Encoding         : 65001

 Date: 09/03/2026 11:06:29
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for budgets
-- ----------------------------
DROP TABLE IF EXISTS `budgets`;
CREATE TABLE `budgets`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `year` int NOT NULL,
  `month` int NOT NULL,
  `budget_amount` decimal(10, 2) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_id`(`user_id`, `year`, `month`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of budgets
-- ----------------------------

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('income','expense') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_user_type_name`(`user_id`, `type`, `name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES (1, 1, 'income', 'รับเงินเดือน', '2026-02-03 16:22:13');
INSERT INTO `categories` VALUES (2, 1, 'income', 'รับงานเสริม', '2026-02-03 16:22:20');
INSERT INTO `categories` VALUES (3, 1, 'income', 'รายรับอื่นๆ', '2026-02-03 16:26:42');
INSERT INTO `categories` VALUES (4, 1, 'expense', 'จ่ายค่าไฟ', '2026-02-03 16:27:06');
INSERT INTO `categories` VALUES (5, 1, 'expense', 'จ่ายค่าน้ำมัน', '2026-02-03 16:27:12');
INSERT INTO `categories` VALUES (6, 1, 'expense', 'จ่ายค่าซ้อมรถ', '2026-02-03 16:27:17');
INSERT INTO `categories` VALUES (7, 1, 'expense', 'จ่ายในครัวเรือน', '2026-02-03 16:27:22');
INSERT INTO `categories` VALUES (8, 1, 'expense', 'จ่ายค่าของจำเป็น', '2026-02-03 16:27:27');
INSERT INTO `categories` VALUES (9, 1, 'expense', 'จ่ายของส่วนตัว', '2026-02-03 16:27:32');
INSERT INTO `categories` VALUES (10, 1, 'expense', 'รายจ่ายอื่นๆ', '2026-02-03 16:27:37');

-- ----------------------------
-- Table structure for transaction_types
-- ----------------------------
DROP TABLE IF EXISTS `transaction_types`;
CREATE TABLE `transaction_types`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'income | expense',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อแสดง เช่น รายรับ รายจ่าย',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uniq_code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Compact;

-- ----------------------------
-- Records of transaction_types
-- ----------------------------
INSERT INTO `transaction_types` VALUES (1, 'income', 'รายรับ', '2026-02-03 17:17:03');
INSERT INTO `transaction_types` VALUES (2, 'expense', 'รายจ่าย', '2026-02-03 17:17:03');

-- ----------------------------
-- Table structure for transactions
-- ----------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` enum('income','expense') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รายรับ / รายจ่าย',
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'หมวดหมู่',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'รายละเอียดเพิ่มเติม',
  `amount` decimal(10, 2) NOT NULL COMMENT 'จำนวนเงิน',
  `trans_date` date NOT NULL COMMENT 'วันที่',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fk_transactions_user`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of transactions
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user','demo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `provider` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `provider_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'active',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (2, 'nittaya', 'user', '$2y$10$hWDZtNSqq8EaQvAsswYhQ.IkJzixUqsJZw7HRnuuP7ZLgI2Q2m4zS', 'นางนิตยา เสาะสาย', '2026-02-01 23:21:28', NULL, NULL, 'active');
INSERT INTO `users` VALUES (3, 'witsanu', 'user', '$2y$10$1Ze.hOVWT622Hue6m0md.ujnpn6BfVjLIYvh7g5.l7ECz3tzy8Yae', 'นายวิษณุ เสาะสาย', '2026-02-03 21:56:15', NULL, NULL, 'active');
INSERT INTO `users` VALUES (1, 'admin', 'admin', '$2y$10$keX0UFtOQOvuRPD3oFJXfuivwRnS6uTnRGhSbDi.WJxT/r.Hxat4S', 'ผู้ดูแลระบบข้อมูล', '2026-02-03 20:30:01', NULL, NULL, 'active');

SET FOREIGN_KEY_CHECKS = 1;
