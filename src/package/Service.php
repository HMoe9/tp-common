<?php
declare (strict_types = 1);

namespace tp\common\package;

use tp\common\package\service\basic\{
    Exception,
    Response,
    Log,
    Redis,
    Variable,
    Hash
};
use tp\common\package\service\{
    BloomFilter,
    TokenBucket
};
use tp\common\package\command\{
    TokenBucket as TokenBucketCommand,
    migrate\MigrateTable
};

class Service extends \think\Service
{
    public $bind = array(
        // 系统核心服务注册
        'exception' => Exception::class,
        'response' => Response::class,
        'system_log' => Log::class,
        'redis' => Redis::class,
        'var' => Variable::class,
        'hash' => Hash::class,

        // 自定义服务
        'bloom_filter' => BloomFilter::class,
        'token_bucket' => TokenBucket::class,
    );

    // 服务注册
    public function register()
    {

    }

    // 服务启动
    public function boot()
    {
        // 注册命令行
        $this->commands([
            TokenBucketCommand::class,
            MigrateTable::class,
        ]);

        // 加载全局变量
        // #1--- 判断当前是否处于调试模式
        $this->app->var->isDebug = false;
        if (($this->app->isDebug()) ||
            ($this->app->config->get('tp-common.app_dev', 0) == 1 &&
                $this->app->config->get('tp-common.app_dev_version', 0) == $this->app->request->param('dev_version')))
        {
            if (!$this->app->isDebug())
            {
                $this->app->debug(); // 启用调试模式
            }
            $this->app->var->isDebug = true;
        }

        // #1.1--- 加载扩展 (自定义) 语言包
        $extend_list = array();
        $lang_path = __DIR__ .  DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
        foreach (scandir($lang_path) as $key => $value)
        {
            if (in_array($value, array('.', '..')))
            {
                continue ;
            }

            $extend_list[$value] = glob($lang_path . $value . DIRECTORY_SEPARATOR . '*.php');
        }

        $langSet = $this->app->lang->getLangSet();
        if (isset($extend_list[$langSet]))
        {
            $this->app->lang->load($extend_list[$langSet]);
            $this->app->var->$langSet = $extend_list[$langSet];
        }
    }
}
