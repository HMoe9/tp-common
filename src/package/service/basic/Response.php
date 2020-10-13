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
     * 日志类型
     * false: error_log
     * true: action_log
     * @var bool
     */
    protected $log_type = false;

    /**
     * 设置 http 状态码
     * @author HMoe9 <hmoe9@qq.com>
     * @param int $http_code
     * @return $this
     */
    public function setHttpCode(int $http_code)
    {
        $this->http_code = $http_code;
        return $this;
    }

    /**
     * 设置返回的 data 内容
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $data
     * @return $this
     */
    public function setData($data = array())
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置异常消息内容
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setException(string $key, $value)
    {
        $this->exception[$key] = $value;
        return $this;
    }

    /**
     * ajax 响应方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     * @param string $behavior
     * @return ThinkResponse
     */
    public function ajaxReturn(string $msg = 'SUCCESS', string $behavior = ''): ThinkResponse
    {
        $error_code = $this->app->exception->getCode($msg); // 错误码
        $error_msg = $this->app->exception->getMessage($error_code); // 错误信息

        // 默认使用错误码里的信息,如果有自定义行为名称使用自定义
        $error_msg = empty($behavior) ? $error_msg : $behavior;
        if ($error_code == '0')
        {
            $this->log_type = true;
        }
        return $this->result($error_code, $error_msg);
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
        // 查询响应的错误信息
        $error_msg = $this->app->exception->getMessage($msg);
        $this->data = array(
            'msg' => $error_msg,
        );
        empty($data) || $this->data = array_merge($this->data, $data);

        $error_code = $this->app->exception->getCode('SUCCESS');
        $error_msg = $this->app->exception->getMessage($error_code);
        return $this->result($error_code, $error_msg);
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
     * 日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $msg
     */
    public function logWrite(string $msg): void
    {
        $var = $this->app->var;
        $log = $this->app->system_log;

        if (!empty($var->batch_log) &&
            is_array($var->batch_log))
        {
            $log->setResponseSchemaField('batch_log', $var->batch_log);
        }

        $log_type = $this->log_type ? 'action_log' : 'error_log';
        $log->{$log_type}($msg);
    }

    /**
     * 请求响应统一方法
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $code
     * @param string $msg
     * @return ThinkResponse
     */
    protected function result(string $code, string $msg): ThinkResponse
    {
        $this->inTransaction($this->log_type); // 事务操作

        $result = array(
            'time' => time(),
            'code' => intval($code),
            'msg'  => $msg,
            'data' => $this->data,
        );
        $response = $this->responseLogSchema($result);
        $this->logWrite($msg);
        return $response;
    }

    /**
     * 响应数据日志格式化
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $result
     * @return ThinkResponse
     */
    protected function responseLogSchema(array $result): ThinkResponse
    {
        $response = json($result, $this->http_code);
        $log_schema = array(
            'header' => $response->getHeader(), // 响应头
            'http_code' => $this->http_code, // http 状态码
        );
        empty($this->data) || $log_schema['data'] = $this->data; // 响应数据
        empty($this->exception) || $log_schema['exception'] = $this->exception; // 异常数据
        $this->app->system_log->setResponseSchema($log_schema);
        return $response;
    }
}
