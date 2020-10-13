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
     * 响应日志结构
     * @var array
     */
    protected $response_schema = array();

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
     * 设置响应日志结构
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $response_schema
     */
    public function setResponseSchema(array $response_schema): void
    {
        $this->response_schema = $response_schema;
    }

    /**
     * 设置响应日志结构字段
     * @author HMoe9 <hmoe9@qq.com>
     * @param string $key
     * @param array $value
     */
    public function setResponseSchemaField(string $key, array $value): void
    {
        $this->response_schema[$key] = $value;
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
        list($action) = $args;

        // 请求响应时间
        $response_time = (microtime(true) - $this->time) * 1000;

        // 内存使用量
        $memory_usage = memory_get_usage() - $this->app->getBeginMem();

        $ins_data = array(
            'node' => $this->request->baseUrl(),
            'action' => $action,
            'remote_ip' => $this->request->ip(),
            'response_time' => $response_time,
            'memory_usage' => $memory_usage,
        );
        $this->logHandle($ins_data);
        $model = self::BIND_MODEL[$method];
        $model::create($ins_data);
    }

    /**
     * 处理日志数据
     * @author HMoe9 <hmoe9@qq.com>
     * @param array $log
     */
    protected function logHandle(array &$log): void
    {
        $log['server'] = array_change_key_case($this->request->server());
        $log['server'] = json_encode($log['server'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $log['request'] = array(
            'param' => $this->request->param(),
            'header' => $this->request->header(),
        );

        // 指定字段过滤,不记录日志
        $filter_field = $this->app->config->get('tp-common.log_filter_field', []);
        if (!empty($filter_field) && is_array($filter_field))
        {
            foreach ($filter_field as $value)
            {
                isset($log['request']['param'][$value]) && $log['request']['param'][$value] = '';
            }
        }
        $log['request'] = json_encode($log['request'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $log['response'] = json_encode($this->response_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
