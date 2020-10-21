<?php
declare (strict_types = 1);

namespace tp\common\package;

use tp\common\package\command\{
    TokenBucket as TokenBucketCommand,
    migrate\MigrateTable
};
use tp\common\package\service\{
    basic\Entity,
    basic\Exception,
    basic\Response,
    basic\Log,
    basic\Redis,
    basic\Variable,
    basic\Hash,
    BloomFilter,
    TokenBucket,
    Database
};

class Service extends \think\Service
{
    public $bind = array(
        'exception' => Exception::class,
        'response' => Response::class,
        'system_log' => Log::class,
        'redis' => Redis::class,
        'var' => Variable::class,
        'hash' => Hash::class,
        'entity' => Entity::class,

        'bloom_filter' => BloomFilter::class,
        'token_bucket' => TokenBucket::class,
    );

    public function register()
    {
        $migrate_table = $this->app->config->get('tp-common.migrate_table', []);
        $stubs_dir = glob(__DIR__ .'/command/migrate/stubs/*.stub');
        foreach ($stubs_dir as $stubs_file)
        {
            $filename = pathinfo($stubs_file, PATHINFO_FILENAME);
            if (!array_key_exists($filename, $migrate_table))
            {
                continue ;
            }

            // 获取自定义表名
            $table = str_replace(' ', '', $migrate_table[$filename]);
            if (empty($table))
            {
                continue ;
            }

            $this->app->bind("tp-common.{$filename}", function () use ($table) {
                return $this->app->invokeClass(Database::class, [$table]);
            });
        }
    }

    public function boot()
    {
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
            $this->app->var->{$langSet} = $extend_list[$langSet];
        }
    }
}
