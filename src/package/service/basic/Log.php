<?php
declare (strict_types = 1);

namespace tp\common\package\service\basic;

use tp\common\package\Base;
use tp\common\package\contract\basic\LogContract;
use tp\common\package\model\log\{
    ActionLogModel,
    ErrorLogModel
};

class Log implements LogContract
{
    use Base;

    /**
     * 日志记录模型
     */
    protected const BIND_MODEL = array(
        'action_log' => ActionLogModel::class, // 操作日志记录
        'error_log' => ErrorLogModel::class, // 错误日志记录
    );

    /**
     * 是否进行日志记录
     * @var bool
     */
    protected $log_write = true;

    /**
     * 是否批量记录
     * @var bool
     */
    protected $batch_write = false;

    /**
     * 批量记录数据集
     * @var array
     */
    protected $batch_data = array(
        'action_log' => array(),
        'error_log' => array(),
    );

    /**
     * 设置日志记录标识
     * @author HMoe9 <hmoe9@qq.com>
     * @param bool $bool
     */
    public function setLogWrite(bool $bool = true): void
    {
        $this->log_write = $bool;
    }

    /**
     * 设置批量记录标识
     * @author HMoe9 <hmoe9@qq.com>
     * @param bool $bool
     */
    public function setBatchWrite(bool $bool = true): void
    {
        $this->batch_write = $bool;
    }

    public function __call($method, $args): void
    {
        // #1--- 判断是否需要记录日志
        // 命令行模式下日志一定记录
        if ((!$this->app->runningInConsole()) &&
            (empty($this->log_write) ||
                ($this->request->isGet() && $method == 'action_log') ||
                empty($this->param)
            ))
        {
            return ;
        }

        // #1.1--- 判断方法是否存在
        if (!array_key_exists($method, self::BIND_MODEL))
        {
            exit('日志记录方法不存在: ' . $method);
        }

        // #1.2--- 日志记录
        if (count($args) == 0)
        {
            $args = array('UNKNOWN ACTION', array());
        }
        elseif (count($args) == 1)
        {
            $args[] = array();
        }
        $this->write($method, $args);
    }

    /**
     * 日志记录
     * @author HMoe9 <hmoe9@qq.com>
     * @param $method
     * @param $args
     */
    public function write($method, $args): void
    {
        list($action, $content) = $args;

        // 请求响应时间
        if (!empty($this->time))
        {
            $response_time = (microtime(true) - $this->time) * 1000;
        }

        // 内存使用量
        $memory_usage = memory_get_usage() - $this->app->getBeginMem();

        $ins_data = array(
            'node' => $this->request->baseUrl(),
            'ip' => $this->request->ip(),
            'action' => $action,
            'content' => $this->secretHandle($content),
            'response_time' => $response_time,
            'memory_usage' => $memory_usage,
        );
        if ($this->batch_write === false)
        {
            $model = self::BIND_MODEL[$method];
            $model::create($ins_data);
        }
        else
        {
            $this->batch_data[$method][] = $ins_data;
        }
    }

    /**
     * 批量记录
     * @author HMoe9 <hmoe9@qq.com>
     */
    public function batchWrite(): void
    {
        if ($this->log_write === false ||
            $this->batch_write === false)
        {
            return ;
        }

        foreach ($this->batch_data as $key => $value)
        {
            if (empty($value))
            {
                continue ;
            }
            $model = self::BIND_MODEL[$key];
            $class = $this->app->make($model);
            $class->saveAll($value);
        }
    }

    /**
     * 处理 param 字段中可能存在的用户密码字段
     * @author HMoe9 <hmoe9@qq.com>
     * @param $content
     * @return string
     */
    protected function secretHandle($content): string
    {
        $content_origin = $content;
        $content = is_array($content) ? $content : json_decode(strval($content), true);
        if ((!is_array($content) && json_last_error() !== JSON_ERROR_NONE) ||
            (is_numeric($content)))
        {
            return is_numeric($content_origin) ? strval($content_origin) : $content_origin;
        }

        isset($content['param']['password']) && $content['param']['password'] = '';
        return json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
