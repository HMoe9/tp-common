<?php
declare (strict_types = 1);

namespace tp\common\package\service;

use tp\common\package\Base;
use InvalidArgumentException;

/**
 * Class BloomFilter
 * 布隆过滤器
 * @author HMoe9 <hmoe9@qq.com>
 * @package tp\common\package\service
 */
class BloomFilter
{
    use Base;

    /**
     * 方法最少数量
     */
    protected const FUNC_NUM = 3;

    /**
     * 设置过滤器的 key
     * @var string
     */
    protected $key = 'bf:';

    /**
     * 是否设置过 key
     * @var bool
     */
    protected $key_sign = false;

    /**
     * 设置哈希函数数组
     * @var array
     */
    protected $hash_func = array(
        'JSHash', 'BKDRHash', 'SDBMHash'
    );

    /**
     * 当前操作的方法
     * @var string
     */
    protected $action = '';

    /**
     * 设置过滤器的 key
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key_sign = true;
        $this->key .= $key;
    }

    /**
     * 设置哈希函数数组
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $arr
     */
    public function setHashFunc(array $arr): void
    {
        $this->hash_func = $arr;
    }

    /**
     * 添加值到指定集合中
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $str
     */
    public function add(string $str): void
    {
        $this->action = __FUNCTION__;
        $this->operate($str);
    }

    /**
     * 判断值是否存在,存在一定存在,不存在有可能存在
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $str
     * @return bool
     */
    public function has(string $str): bool
    {
        $this->action = __FUNCTION__;
        $result = $this->operate($str);

        foreach ($result as $value)
        {
            if ($value == 0)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * 统一操作
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $str
     * @return array
     */
    protected function operate(string $str): array
    {
        $this->prepend();

        $this->app->redis->multi();
        foreach ($this->hash_func as $func)
        {
            $hash = $this->app->hash->$func($str);
            switch ($this->action)
            {
                case 'add':
                    $this->app->redis->setBit($this->key, $hash, 1);
                    break;
                case 'has':
                    $this->app->redis->getBit($this->key, $hash);
                    break;
            }
        }

        return $this->app->redis->exec();
    }

    /**
     * 操作前准备
     * @author HMoe9 <hmoe9@qq.com>
     */
    protected function prepend(): void
    {
        if ($this->key_sign === false)
        {
            throw new InvalidArgumentException('BLOOM_FILTER_KEY_REQUIRE');
        }
        if (empty($this->hash_func))
        {
            throw new InvalidArgumentException('BLOOM_FILTER_FUNC_REQUIRE');
        }

        foreach ($this->hash_func as $key => $value)
        {
            if (!method_exists($this->app->hash, $value))
            {
                unset($this->hash_func[$key]);
            }
        }

        if (count($this->hash_func) < self::FUNC_NUM)
        {
            throw new InvalidArgumentException('BLOOM_FILTER_FUNC_TOO_FEW');
        }
    }
}
