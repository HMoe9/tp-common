<?php
declare (strict_types = 1);

namespace tp\common\package\command;

use think\console\{
    Input, Output, Command
};
use think\console\input\Option;
use RuntimeException;

class TokenBucket extends Command
{
    /**
     * 默认执行毫秒数
     */
    protected const MS = 100;

    /**
     * 默认添加令牌数
     */
    protected const NUM = 1;

    protected function configure()
    {
        $this->setName('tp-common:token_bucket')
            ->addOption('key', null, Option::VALUE_REQUIRED, '', null)
            ->addOption('ms', null, Option::VALUE_REQUIRED, '', self::MS)
            ->addOption('num', null, Option::VALUE_REQUIRED, '', self::NUM)
            ->setDescription('token bucket');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!extension_loaded('swoole'))
        {
            throw new RuntimeException('swoole 扩展未安装');
        }

        $key = $input->getOption('key') ?? $output->error('key 不能为空');
        $ms = intval($input->getOption('ms'));
        $num = intval($input->getOption('num'));
        if ($ms < self::MS)
        {
            $output->error('最小执行毫秒数不能低于: ' . self::MS);
            return false;
        }

        if ($num < self::NUM)
        {
            $output->error('最小生成令牌数不能少于: ' . self::NUM);
            return false;
        }

        // 初始化令牌桶
        $this->app->token_bucket->setKey($key);
        $this->app->token_bucket->reset();

        // 指定时间间隔添加令牌
        swoole_timer_tick($ms, function($timer_id, $num) {
           $this->app->token_bucket->add($num);
        }, $num);
    }
}
