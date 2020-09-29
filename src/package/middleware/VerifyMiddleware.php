<?php
declare (strict_types = 1);

namespace tp\common\package\middleware;

use tp\common\package\Base;
use InvalidArgumentException;
use think\exception\ValidateException;

/**
 * Class VerifyMiddleware
 * 参数验证中间件
 * @author HMoe9 <hmoe9@qq.com>
 * @package tp\common\package\middleware
 */
class VerifyMiddleware
{
    use Base;

    /**
     * 实例化的验证类
     * @var string
     */
    protected $validClass = '';

    public function handle($request, \Closure $next)
    {
        $this->prepare();

        // 实例化验证类对象
        $validate = $this->app->make($this->validClass);

        // 调试模式下自动启用批量验证
        $result = $validate->batch($this->app->var->isDebug)
            ->scene($request->action(true))
            ->check($request->param());
        if ($result !== true)
        {
            throw new ValidateException($validate->getError());
        }

        return $next($request);
    }

    /**
     * 验证前准备
     * @author HMoe9 <hmoe9@qq.com>
     */
    protected function prepare(): void
    {
        $controller = $this->request->controller(true); // 控制器名
        $action = $this->request->action(true); // 方法名
        $method = strtolower($this->request->method()); // 请求类型
        $module_verify_method = $this->app->config->get('verify_method'); // 当前模块验证规则

        // #1--- 判断验证规则是否存在
        $verify_method = $module_verify_method;
        if (empty($verify_method))
        {
            throw new InvalidArgumentException('VERIFY_METHOD_CONFIG_NOT_EXISTS');
        }

        // #1.1--- 所有 key 转小写
        $this->lowerCase($verify_method);

        // #1.2--- 判断当前控制器是否存在
        if (!array_key_exists($controller, $verify_method))
        {
            throw new InvalidArgumentException('VERIFY_CONTROLLER_NOT_EXISTS');
        }

        // #1.3--- 判断当前方法是否存在
        if (!array_key_exists($action, $verify_method[$controller]))
        {
            throw new InvalidArgumentException('VERIFY_METHOD_NOT_EXISTS');
        }

        // #1.4--- 判断请求类型
        $method_key = strtolower($verify_method[$controller][$action]);
        if ($method != $method_key)
        {
            throw new InvalidArgumentException('REQUEST_TYPE_ERROR');
        }

        // #1.5--- 校验验证类是否存在
        $this->validClass = '\\' . $this->app->parseClass('validate', $controller . 'Validate');
        if (!class_exists($this->validClass))
        {
            throw new InvalidArgumentException('VALIDATE_CLASS_NOT_EXISTS');
        }
    }

    /**
     * 所有 key 转小写
     * @author HMoe9 <hmoe9@qq.com>
     * @param $data
     * @return array
     */
    protected function lowerCase(&$data): array
    {
        $data = array_change_key_case($data, CASE_LOWER);
        foreach ($data as $key => $value)
        {
            if (is_array($value))
            {
                $this->lowerCase($data[$key]);
            }
        }

        return $data;
    }
}
