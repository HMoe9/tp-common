<?php
declare(strict_types=1);

namespace tp\common\package\job;

use think\App;
use tp\common\package\model\job\{
    SuccessJobsModel,
    FailedJobsModel
};
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
    public function success()
    {
        $ins_data = array(
            'job_id' => $this->job->getJobId(),
            'connection' => $this->job->getConnection(),
            'queue' => $this->job->getQueue(),
            'payload' => $this->job->getRawBody(),
        );
        SuccessJobsModel::create($ins_data);
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

        $ins_data = array(
            'connection' => $this->job->getConnection(),
            'queue' => $this->job->getQueue(),
            'payload' => $this->job->getRawBody(),
            'exception' => json_encode($exception, JSON_UNESCAPED_UNICODE),
        );
        FailedJobsModel::create($ins_data);

        if (!empty($this->app->var->error_log))
        {
            $this->app->response->errorLogWrite($exception['Exception'], $exception);
        }
    }
}
