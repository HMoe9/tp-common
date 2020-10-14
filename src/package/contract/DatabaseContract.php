<?php
declare (strict_types = 1);

namespace tp\common\package\contract;

interface DatabaseContract
{
    /**
     * 日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $data
     */
    public function log(array $data): void;
}
