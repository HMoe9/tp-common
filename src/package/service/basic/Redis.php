<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use think\cache\driver\Redis as BaseRedis;
use tp\common\package\contract\basic\RedisContract;

class Redis implements RedisContract
{
    use Base;

    /**
     * redis 实例
     * @var BaseRedis
     */
    protected $redis;

    /**
     * 锁的键名
     * @var string
     */
    protected $lock_key = 'lock';

    /**
     * 锁超时时间
     * @var int
     */
    protected $expire_time = 3;

    /**
     * 标识
     * @var string
     */
    protected $identify;

    protected function initialize()
    {
        $env = $this->app->env;
        $options = array(
            'host' => $env->get('redis.host', '127.0.0.1'),
            'port' => $env->get('redis.port', 6379),
            'password' => $env->get('redis.password', ''),
            'timeout' => $env->get('redis.timeout', 0),
            'select' => $env->get('redis.select', 0),
        );
        $this->redis = new BaseRedis($options);
        $this->prepare();
    }

    /**
     * @param string $lock_key
     */
    public function setLockKey(string $lock_key): void
    {
        $this->lock_key = $lock_key;
    }

    /**
     * @param int $expire_time
     */
    public function setExpireTime(int $expire_time): void
    {
        $this->expire_time = $expire_time;
    }

    public function prepare(): void
    {
        $config = $this->app->config;
        $extra = $config->get('tp-common.redis', []);
        foreach ($extra as $key => $value)
        {
            if (property_exists($this, $key))
            {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 加锁
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    public function lock(): bool
    {
        $identify = uniqid();
        $result = $this->redis->rawCommand('SET', $this->lock_key, $identify, 'NX', 'EX', $this->expire_time);
        if ($result)
        {
            $this->identify = $identify;
            return true;
        }
        return false;
    }

    /**
     * 释放锁
     * @author HMoe9 <hmoe9@qq.com>
     * @return bool
     */
    public function unlock(): bool
    {
        if (empty($this->identify))
        {
            return false;
        }

        $lua = <<<LUA
    if redis.call('get', KEYS[1]) == ARGV[1] then
        return redis.call('del', KEYS[1]) 
    else 
        return 0 
    end
LUA;
        // 原子操作
        $result = $this->redis->eval($lua, array($this->lock_key, $this->identify), 1);
        if ($result)
        {
            return true;
        }
        return false;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->redis, $method), $args);
    }
}
