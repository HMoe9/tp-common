<?php
declare (strict_types = 1);

namespace tp\common\package\model\job;

use tp\common\package\Model;

class FailedJobsModel extends Model
{
    /**
     * 数据表表名
     */
    protected $name = 'failed_jobs';
}
