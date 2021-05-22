<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use ArrayAccess;

class Variable implements ArrayAccess
{
    /**
     * 对象属性容器
     * @var array
     */
    protected $attr = array(
        'batch_log' => array(), // 批量日志
    );

    /**
     * @return array
     */
    public function getAttr(): array
    {
        return $this->attr;
    }

    /**
     * 统一获取方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key))
        {
            return $this->attr[$key];
        }

        return $default;
    }

    /**
     * 通过数组的方式访问对象时判断key是否存在
     * @author HMoe9 <hmoe9@qq.com>
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attr[$offset]);
    }

    /**
     * 通过数组的方式访问对象属性
     * @author HMoe9 <hmoe9@qq.com>
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * 通过数组的方式设置对象属性
     * @author HMoe9 <hmoe9@qq.com>
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->attr[$offset] = $value;
    }

    /**
     * 通过数组的方式设置对象属性
     * @author HMoe9 <hmoe9@qq.com>
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->attr[$offset]);
    }

    public function __set($key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key): bool
    {
        return $this->offsetExists($key);
    }

    public function __unset($key)
    {
        $this->offsetUnset($key);
    }
}
