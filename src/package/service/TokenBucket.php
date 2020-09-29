<?php
declare (strict_types = 1);

namespace tp\common\package\service;

use tp\common\package\Base;
use InvalidArgumentException;

/**
 * Class TokenBucket
 * 令牌桶
 * @author HMoe9 <hmoe9@qq.com>
 * @package tp\common\package\service
 */
class TokenBucket
{
    use Base;

    /**
     * 最大令牌数
     * @var int
     */
    protected $max_num = 100;

    /**
     * 令牌桶 key
     * @var string
     */
    protected $key = 'tb:';

    /**
     * 是否设置过 key
     * @var bool
     */
    protected $key_sign = false;

    /**
     * 设置令牌桶 key
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key_sign = true;
        $this->key .= $key;
    }

    /**
     * 设置最大令牌数
     * @author HMoe9 <hmoe9@qq.com>
     * @param int $num
     */
    public function setMaxNum(int $num = 100): void
    {
        $this->max_num = abs($num);
    }

    /**
     * 添加令牌
     * @author HMoe9 <hmoe9@qq.com>
     * @param int $num
     */
    public function add(int $num = 1): void
    {
        $this->prepend();
        $len = $this->app->redis->lLen($this->key);
        $num = $this->max_num >= ($len + $num) ? $num : ($this->max_num - $len);
        if ($num > 0)
        {
            $token = array_fill(0, $num, 1);
            $this->app->redis->lPush($this->key, ...$token);
        }
    }

    /**
     * 获取令牌
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    public function get(): bool
    {
        $this->prepend();
        return $this->app->redis->rPop($this->key) ? true : false;
    }

    /**
     * 重置令牌桶
     * @author HMoe9 <hmoe9@qq.com>
     */
    public function reset(): void
    {
        $this->prepend();
        $this->app->redis->del($this->key);
        $this->add($this->max_num);
    }

    /**
     * 操作前准备
     * @author HMoe9 <hmoe9@qq.com>
     */
    protected function prepend(): void
    {
        if ($this->key_sign === false)
        {
            throw new InvalidArgumentException('TOKEN_BUCKET_KEY_REQUIRE');
        }
    }
}
