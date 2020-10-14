<?php

return array(
    'app_dev' => true, // 调试模式
    'app_dev_version' => '1.0', // 调试模式匹配参数

    'http_code' => 500, // 抛出异常时 http 状态码

    // 校验表是否存在
    // false: 如果表不存在,报错
    // true: 如果表不存在,跳过日志记录操作
    'table_exist_verify' => false,

    // 组件包使用的基础表
    // stub 名 => 表名可修改(不含前缀)
    'migrate_table' => array(
        'action_log' => 'common_action_log', // 请求日志
        'error_log' => 'common_error_log', // 异常日志
        'failed_jobs' => 'common_failed_jobs', // 失败队列日志
        'success_jobs' => 'common_success_jobs', // 成功队列日志
    ),

    // 日志记录时,过滤请求参数中的字段
    'log_filter_field' => array(
        'password', 'id_card',
    ),
);
