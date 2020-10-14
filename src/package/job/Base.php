<?php
declare(strict_types=1);

namespace tp\common\package\job;

use think\App;
use Throwable;
use think\db\exception\DbException;

class Base
{
    protected $job;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * 操作成功日志记录
     * @author HMoe9 <hmoe9@qq.com>
     */
    public function success(): void
    {
        if ($this->app->has("tp-common.success_jobs"))
        {
            $ins_data = array(
                'job_id' => $this->job->getJobId(),
                'connection' => $this->job->getConnection(),
                'queue' => $this->job->getQueue(),
                'payload' => $this->job->getRawBody(),
            );
            $this->app->make("tp-common.success_jobs")->log($ins_data);
        }
    }

    /**
     * 异常捕获
     * @author HMoe9 <hmoe9@qq.com>
     * @param Throwable $e
     */
    public function error(Throwable $e): void
    {
        $exception = array();
        if ($e instanceof DbException)
        {
            $exception['DbException'] = $e->getData();
        }
        $exception['Exception'] = $e->getMessage();
        $exception['Code'] = $e->getCode();
        $exception['File'] = $e->getFile();
        $exception['Line'] = $e->getLine();

        if ($this->app->has("tp-common.failed_jobs"))
        {
            $ins_data = array(
                'connection' => $this->job->getConnection(),
                'queue' => $this->job->getQueue(),
                'payload' => $this->job->getRawBody(),
                'exception' => json_encode($exception, JSON_UNESCAPED_UNICODE),
            );
            $this->app->make("tp-common.failed_jobs")->log($ins_data);
        }
        if (!empty($this->app->var->batch_log))
        {
            $this->app->response->LogWrite($exception['Exception']);
        }
    }
}
