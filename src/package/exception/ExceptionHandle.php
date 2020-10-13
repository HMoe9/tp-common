<?php
declare (strict_types = 1);

namespace tp\common\package\exception;

use think\exception\{
    Handle,
    ValidateException
};
use think\Response;
use Throwable;
use think\db\exception\DbException;
use InvalidArgumentException;
use LogicException;

/**
 * Class ExceptionHandle
 * 异常处理类
 * @author HMoe9 <hmoe9@qq.com>
 * @package tp\common\package\exception
 */
class ExceptionHandle extends Handle
{
    protected $http_code; // http 的状态码

    /**
     * 重写异常处理类
     * @author HMoe9 <hmoe9@qq.com>
     * @param $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 调试模式
        if ($this->app->var->isDebug)
        {
            return parent::render($request, $e);
        }

        $this->http_code = $this->app->config->get('tp-common.http_code', 500);

        // 异常处理
        // InvalidArgumentException extends LogicException
        // LogicException extends Exception
        // 判断顺序不能颠倒
        if (($e instanceof InvalidArgumentException) ||
            ($e instanceof ValidateException))
        {
            // 不做日志记录操作
            $this->app->system_log->setLogWrite(false);
        }
        elseif ($e instanceof LogicException)
        {
            $this->app->system_log->setLogWrite(boolval($e->getCode()));
            return $this->app->response->ajaxReturn('INTERNAL_SERVER_ERROR', $e->getMessage());
        }
        elseif ($e instanceof HttpExceptions)
        {
            // http 异常请求处理
            $this->http_code = $e->getStatusCode() ?: $this->http_code;
            $e->getData() && $this->app->response->setData($e->getData()); // false 就不记录错误信息
        }
        elseif ($e instanceof DbException)
        {
            // db 类的异常需要记录错误的操作内容
            $this->app->response->setException('DbException', $e->getData());
        }

        return $this->app->response->setException('Exception', $e->getMessage()) // 异常消息内容
            ->setException('Code', $e->getCode()) // 异常代码
            ->setException('File', $e->getFile()) // 创建异常时的程序文件名称
            ->setException('Line', $e->getLine()) // 获取创建的异常所在文件中的行号
            ->setHttpCode($this->http_code) // 设置 http 状态码
            ->exceptionReturn($e); // 异常响应
    }

    /**
     * 重写父类方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param Throwable $e
     * @return string
     */
    protected function getMessage(Throwable $e): string
    {
        $message = parent::getMessage($e);

        // 如果相等说明不是系统定义的,返回自定义的错误信息
        if ($message == $e->getMessage())
        {
            // 判断异常的来源
            // 1. ValidateException 类抛出的异常
            // 2. 其他异常
            if ($e instanceof ValidateException)
            {
                // 判断验证异常内容是否是批量验证
                $error = $e->getError();
                foreach ($error as &$value)
                {
                    $value = $this->app->exception->getMessage($value);
                }
                $message = urldecode(str_replace(['=', '&'], [': ', PHP_EOL], http_build_query($error)));
            }
            else
            {
                $error_code = $this->app->exception->getCode($e->getMessage()); // 错误码
                if ($error_code != '-1')
                {
                    $message = $this->app->exception->getMessage($error_code);
                }
            }
        }

        return $message;
    }
}
