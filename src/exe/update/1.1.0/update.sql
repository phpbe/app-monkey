
DROP TABLE IF EXISTS `monkey_push_driver`;
DROP TABLE IF EXISTS `monkey_push_task`;
DROP TABLE IF EXISTS `monkey_push_task_log`;


CREATE TABLE `monkey_push_driver` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`pull_driver_id` varchar(36) NOT NULL DEFAULT '' COMMENT '采集器ID',
`name` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
`url` varchar(300) NOT NULL DEFAULT '' COMMENT '发布网址',
`headers` text NOT NULL COMMENT '头数据',
`format` varchar(30) NOT NULL DEFAULT 'form' COMMENT  '请求格式（form/json）',
`fields` text NOT NULL COMMENT '字段',
`interval` int(11) NOT NULL DEFAULT '1000' COMMENT '间隔时间（毫秒）',
`ordering` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
`status` varchar(30) NOT NULL DEFAULT 'create' COMMENT '状态（create - 新建 / pending - 即将运行 / running - 动行中 / finish - 完成 / error - 执行出错）',
`message` varchar(500) NOT NULL DEFAULT '' COMMENT '信息',
`is_enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用',
`is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='发布任务';

ALTER TABLE `monkey_push_driver`
ADD PRIMARY KEY (`id`),
ADD KEY `pull_driver_id` (`pull_driver_id`);


CREATE TABLE `monkey_push_driver_log` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`push_driver_id` varchar(36) NOT NULL DEFAULT '' COMMENT '发布任务ID',
`content_id` varchar(36) NOT NULL DEFAULT '' COMMENT '内容ID',
`request` text NOT NULL COMMENT '请求数据',
`response` text NOT NULL COMMENT '响应数据',
`success` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否成功',
`message` varchar(500) NOT NULL COMMENT '信息',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='发布任务日志';

ALTER TABLE `monkey_push_driver_log`
ADD PRIMARY KEY (`id`),
ADD KEY `push_driver_id` (`push_driver_id`,`content_id`);
