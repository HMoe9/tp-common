<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

class Variable
{
    /**
     * 批量日志
     * @var array
     */
    public $batch_log = array();

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (!property_exists($this, $name))
        {
            return null;
        }
        return $this->$name;
    }

    public function __isset($name): bool
    {
        $value = $this->$name;

        return !empty($value);
    }
}
