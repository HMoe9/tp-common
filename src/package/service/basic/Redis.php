<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use think\cache\driver\Redis as BaseRedis;

class Redis
{
    use Base;

    /**
     * redis 实例
     * @var BaseRedis
     */
    protected $redis;

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
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->redis, $method), $args);
    }
}
