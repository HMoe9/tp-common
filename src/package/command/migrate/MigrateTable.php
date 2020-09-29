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

        $migrate_table = $this->app->config->get('tp-common.migrate_table', '');
        if (empty($migrate_table))
        {
            $this->output->error('The migration file does not exist');
        }

        $this->output->info('executing ...');
        $creator = $this->app->get('migration.creator');
        foreach ($migrate_table as $table)
        {
            $className = Str::studly("create_{$table}_table");
            $stub_file = __DIR__ . "/stubs/{$table}.stub";
            if (!file_exists($stub_file))
            {
                continue ;
            }

            $path = $creator->create($className);
            $contents = file_get_contents($stub_file);
            $contents = strtr($contents, [
                '{{table}}' => $table,
            ]);
            file_put_contents($path, $contents);
            sleep(1);
        }

        $this->output->info('Migration created successfully!');
    }
}
