<?php
declare (strict_types = 1);

namespace tp\common\package;

use ReflectionClass;

trait Base
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 请求参数
     * @var array
     */
    protected $param = array();

    /**
     * 请求时间
     * @var int
     */
    protected $time = 0;

    /**
     * 实例化参数
     * @var array
     */
    protected $args = array();

    public function __construct()
    {
        $this->args = func_get_args();
        $this->invoke();

        $this->app = app();
        $this->time = intval($this->app->getBeginTime());
        $this->request = $this->app->request;
        $this->param = $this->request->param();

        // 初始化方法
        $this->initialize();
    }

    /**
     * 初始化方法
     * @author HMoe9 <hmoe9@qq.com>
     */
    protected function initialize()
    {}

    /**
     * 递归执行父类构造
     * @author HMoe9 <hmoe9@qq.com>
     * @param null $class
     * @param bool $recursion
     * @throws \ReflectionException
     */
    protected function invoke($class = null, bool $recursion = false)
    {
        $class = $class ?? $this;
        $reflect = new ReflectionClass($class);

        // 判断当前类是否有父类
        $parent = $reflect->getParentClass();
        if ($parent !== false)
        {
            $recursion = $this->invoke($parent->name, true);
        }

        // 有超类的情况下不能有构造
        // 没超类的情况下,如果父类有构造要先执行父类构造
        $trait = $reflect->getTraits();
        $constructor = $reflect->getConstructor();
        if (empty($trait) && !is_null($constructor) && $recursion)
        {
            $reflect->newInstance(...$this->args);
        }
    }

    /**
     * 设置自定义变量
     * @author HMoe9 <hmoe9@qq.com>
     * @param $name
     * @param $value
     */
    public function __set($name, $value): void
    {
        $this->app->var->{$name} = $value;
    }

    /**
     * 获取自定义变量
     * @author HMoe9 <hmoe9@qq.com>
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->app->var->{$name};
    }

    /**
     * 当对不可访问属性调用 isset 和 empty 时触发
     * @author HMoe9 <hmoe9@qq.com>
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        $value = $this->{$name};

        return !empty($value);
    }
}
