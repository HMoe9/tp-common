<?php
declare (strict_types = 1);

namespace tp\common\package\contract\basic;

interface RedisContract
{
    /**
     * 加锁
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    public function lock(): bool;

    /**
     * 释放锁
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    public function unlock(): bool;
}
