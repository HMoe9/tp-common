<?php
declare (strict_types = 1);

namespace tp\common\package\contract;

use think\queue\Job;
use Throwable;

interface JobContract
{
    /**
     * 队列消费
     * @author HMoe9 <hmoe9@qq.com>
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data): void;

    /**
     * 队列异常操作
     * @author HMoe9 <hmoe9@qq.com>
     * @param $data
     * @param Throwable $e
     */
    public function failed($data, Throwable $e): void;
}
