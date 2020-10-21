<?php
declare (strict_types = 1);

namespace tp\common\package\command\migrate;

use think\console\Command;
use think\helper\Str;
use think\migration\Creator;

class MigrateTable extends Command
{
    protected function configure()
    {
        $this->setName('tp-common:migrate-table')
            ->setDescription('Create a migration table');
    }

    public function handle()
    {
        if (!$this->app->has('migration.creator'))
        {
            $this->output->error('Install think-migration first please');
            return;
        }

        $migrate_table = $this->app->config->get('tp-common.migrate_table', []);
        if (empty($migrate_table))
        {
            $this->output->error('The migration file does not exist');
        }

        $this->output->info('executing ...');

        $creator = $this->app->get('migration.creator');
        $stubs_dir = glob(__DIR__ . '/stubs/*.stub');
        foreach ($stubs_dir as $stubs_file)
        {
            $filename = pathinfo($stubs_file, PATHINFO_FILENAME);
            if (!array_key_exists($filename, $migrate_table))
            {
                continue ;
            }

            // 获取自定义表名, 表名为空跳过
            $table = str_replace(' ', '', $migrate_table[$filename]);
            if (empty($table))
            {
                continue ;
            }

            sleep(1);
            $className = Str::studly("create_{$table}_table"); // 下划线转驼峰
            $path = $creator->create($className);
            $contents = file_get_contents($stubs_file);
            $contents = strtr($contents, [
                '{{className}}' => $className,
                '{{table}}' => $table,
            ]);
            file_put_contents($path, $contents);
            $this->output->info("{$className} created successfully!");
        }

        $this->output->info('Migration created successfully!');
    }
}
