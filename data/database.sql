
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for t_action_log
-- ----------------------------
DROP TABLE IF EXISTS `t_action_log`;
CREATE TABLE `t_action_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `node` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `action` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作行为名称',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作内容描述',
  `response_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '请求响应时间 (ms)',
  `memory_usage` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内存使用量 (byte)',
  `create_time` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '客户行为日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_error_log
-- ----------------------------
DROP TABLE IF EXISTS `t_error_log`;
CREATE TABLE `t_error_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `node` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `action` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'success' COMMENT '操作行为名称',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作内容描述',
  `response_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '请求响应时间 (ms)',
  `memory_usage` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '内存使用量 (byte)',
  `create_time` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'api错误日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `t_failed_jobs`;
CREATE TABLE `t_failed_jobs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `connection` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `queue` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `payload` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `exception` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `fail_time` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '失败的队列信息记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_success_jobs
-- ----------------------------
DROP TABLE IF EXISTS `t_success_jobs`;
CREATE TABLE `t_success_jobs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '队列id',
  `connection` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '队列的方式 redis 或 database',
  `queue` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '队列名称',
  `payload` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '队列参数',
  `create_time` datetime(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '成功的队列信息记录表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
