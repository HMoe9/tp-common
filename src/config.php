<?php

use tp\common\package\model\{
    job\FailedJobsModel,
    job\SuccessJobsModel,
    log\ActionLogModel,
    log\ErrorLogModel
};

return [
    'error_http_code' => 500, // 抛出异常时 http 状态码

    // 组件包使用的基础表
    // 模型名 => 表名(不含前缀)
    'migrate_table' => array(
        FailedJobsModel::class => 'failed_jobs',
        SuccessJobsModel::class => 'success_jobs',
        ActionLogModel::class => 'action_log',
        ErrorLogModel::class => 'error_log',
    ),

    'app_dev' => true, // 调试模式
    'app_dev_version' => '1.0', // 调试模式匹配参数
];
