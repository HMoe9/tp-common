<?php
declare (strict_types = 1);

namespace tp\common\package\contract\basic;

interface LogContract
{
    /**
     * 日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param $method
     * @param $args
     */
    public function write($method, $args): void;
}
