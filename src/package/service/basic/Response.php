<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use tp\common\package\contract\basic\ResponseContract;
use Throwable;
use think\facade\Db;
use think\Response as ThinkResponse;

class Response implements ResponseContract
{
    use Base;

    /**
     * http 的状态码
     * @var int
     */
    protected $http_code = 200;

    /**
     * 响应的 json 数据
     * @var array
     */
    protected $data = array();

    /**
     * 自定义异常消息内容
     * @var array
     */
    protected $exception = array();

    /**
     * 设置 http 状态码
     * @author HMoe9 <hmoe9@qq.com>
     * @param int $http_code
     */
    public function setHttpCode(int $http_code): void
    {
        $this->http_code = $http_code;
    }

    /**
     * 设置返回的 data 内容
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $data
     */
    public function setData($data = array()): void
    {
        $this->data = $data;
    }

    /**
     * 设置异常消息内容
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $key
     * @param $value
     */
    public function setException(string $key, $value): void
    {
        $this->exception[$key] = $value;
    }

    /**
     * ajax 统一响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     * @param string $behavior
     * @return ThinkResponse
     */
    public function ajaxReturn(string $msg = 'SUCCESS', string $behavior = ''): ThinkResponse
    {
        $error_code = $this->app->exception->getCode($msg); // 错误码
        $error_msg = $this->app->exception->getMessage($error_code); // 错误信息
        $log = array(
            'data' => $this->data,
            'param' => $this->param,
        );

        // 默认使用错误码里的信息,如果有自定义行为名称使用自定义
        $error_msg = empty($behavior) ? $error_msg : $behavior;
        if ($error_code == '0')
        {
            $this->inTransaction(true); // 事务操作
            $this->app->system_log->action_log($error_msg, $log);
            return $this->success($error_msg);
        }
        else
        {
            $this->errorLogWrite($error_msg, $log);
            return $this->result($error_code, $error_msg);
        }
    }

    /**
     * 警告响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     * @param array $data
     * @return ThinkResponse
     */
    public function warningReturn(string $msg = 'INTERNAL_SERVER_ERROR', array $data = array()): ThinkResponse
    {
        $this->inTransaction(false); // 事务操作

        // 查询响应的错误信息
        $error_msg = $this->app->exception->getMessage($msg);
        $this->data = array(
            'msg' => $error_msg,
        );
        empty($data) || $this->data = array_merge($this->data, $data);

        $log = array(
            'data' => $this->data,
            'param' => $this->param,
        );
        $this->errorLogWrite($error_msg, $log);

        $error_msg = $this->app->exception->getMessage('SUCCESS');
        return $this->success($error_msg);
    }

    /**
     * 异常处理统一响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param Throwable $e
     * @return ThinkResponse
     */
    public function exceptionReturn(Throwable $e): ThinkResponse
    {
        $error_code = $this->app->exception->getCode($e->getMessage()); // 错误码

        // 错误码为 -1 的时候有两种情况
        // 1. 没有定义错误码
        // 2. 可能是运行错误
        $error_code == '-1' && $error_code = '10000'; // 系统异常错误码

        $error_msg = $this->app->exception->getMessage($error_code); // 错误信息
        $log = array(
            'data' => $this->data,
            'param' => $this->param,
            'exception' => $this->exception,
        );

        $this->errorLogWrite($error_msg, $log);
        $this->setData(); // 异常处理不返回数据,只进行数据记录
        return $this->result($error_code, $error_msg);
    }

    /**
     * 事务提交/回滚操作
     * @author HMoe9 <hmoe9@qq.com>
     * @param bool $bool
     */
    public function inTransaction(bool $bool = true): void
    {
        // 检查是否在一个事务内,如果在事务内,抛出异常后回滚事务
        $pdo = Db::getPdo();
        if ($pdo !== false)
        {
            if ($pdo->inTransaction() === true)
            {
                $bool ? Db::commit() : Db::rollback();
            }
        }
    }

    /**
     * 错误日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $error_msg
     * @param $log
     */
    public function errorLogWrite(string $error_msg, $log): void
    {
        // var 内的 error_log 自定义变量,二维数组。
        // 格式为: array(array('错误信息', '具体数据'), ...)
        // error_log 不存在,直接记录错误日志。
        // error_log 存在,并且是二维数组,进行批量记录。
        if (empty($this->app->var->error_log))
        {
            $this->app->system_log->error_log($error_msg, $log);
        }
        elseif (is_array($this->app->var->error_log))
        {
            $this->app->var->error_log[] = array(
                $error_msg, $log
            );
            $this->app->system_log->setBatchWrite();
            foreach ($this->app->var->error_log as $value)
            {
                $this->app->system_log->error_log(...$value);
            }
            $this->app->system_log->batchWrite();
        }
    }

    /**
     * 操作成功响应
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     * @return ThinkResponse
     */
    protected function success(string $msg): ThinkResponse
    {
        $result = array(
            'time' => time(),
            'code' => 0,
            'msg'  => $msg,
            'data' => $this->data,
        );
        return json($result, $this->http_code);
    }

    /**
     * 操作失败响应
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $code
     * @param string $msg
     * @return ThinkResponse
     */
    protected function result(string $code, string $msg): ThinkResponse
    {
        $result = array(
            'time' => time(),
            'code' => (int)$code,
            'msg'  => $msg,
            'data' => $this->data,
        );
        return json($result, $this->http_code);
    }
}
