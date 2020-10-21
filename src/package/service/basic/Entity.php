<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use RuntimeException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class Entity
{
    use Base;

    protected $instances = array();

    public function getInstances(): array
    {
        return $this->instances;
    }

    public function has($name): bool
    {
        return isset($this->instances[$name]);
    }

    public function get(string $name)
    {
        if ($this->has($name))
        {
            return $this->instances[$name];
        }
        throw new RuntimeException('CLASS_NOT_EXISTS');
    }

    public function jsonToObject(string $class, array $param = array(), bool $newInstance = false)
    {
        if ($this->has($class))
        {
            return $this->instances[$class];
        }

        $reflect = new ReflectionClass($class);
        $instance = $reflect->newInstance();
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method)
        {
            if (preg_match('/set(\w+)/', $method->getName(), $matches))
            {
                $param = $param ?: $this->param;
                $this->invokeSetterMethod($matches[1], $reflect, $instance, $param);
            }
        }

        if (!$newInstance)
        {
            $this->instances[$class] = $instance;
        }
        return $instance;
    }

    public function mapToObject(string $class, array $map, $toArray = false)
    {
        $instances = array(); // 实体对象
        $arr = array(); // 数组
        foreach ($map as $value)
        {
            $instance = $this->jsonToObject($class, $value, true);
            $instances[] = $instance;
            $arr[] = $instance->getData();
        }

        $this->instances[$class] = $instances;
        if ($toArray)
        {
            return $arr;
        }
        return $instances;
    }

    protected function invokeSetterMethod($name, ReflectionClass $reflect, &$instance, array $param = array()): void
    {
        // ex: 把 GoodsId 转化为 goods_id
        $prop_name = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $name));

        $props = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($props as $prop)
        {
            if (strtolower($prop->getName()) == $prop_name)
            {
                $method = $reflect->getMethod('set' . $name);
                $args = $method->getParameters();
                if (count($args) == 1 && array_key_exists($prop_name, $param))
                {
                    $method->invoke($instance, $param[$prop_name]);

                    // 通过注释判断是否是表字段
                    $comment = $prop->getDocComment();
                    if ($comment && preg_match('/\@Column\s?/', $comment))
                    {
                        $instance->setAttr($prop_name, $param[$prop_name]);
                    }
                }
            }
        }
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }
}
