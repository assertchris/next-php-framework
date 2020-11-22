<?php

namespace Next\Commands;

class MigrateCommand extends \Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        // TODO
    }

    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output,
    ) {
        $connection = \Next\App::getInstance()->make('db');

        $count = 0;
        $output->writeln('Migrating');

        if (!$connection->schema()->hasTable('migrations')) {
            $output->writeln('Creating migrations table');

            $connection->schema()->create('migrations', function($table) {
                $table->string('name')->unique();
            });
        }
        
        $path = path('migrations');
        $migrations = files($path);
        
        foreach ($migrations as $migration) {
            $name = basename($migration, '.php');
        
            if ($connection->table('migrations')->where('name', $name)->first()) {
                continue;
            }

            $count++;
            $output->writeln("Migrating {$name}");
        
            $runner = require $migration;
            $runner($connection);
        
            $connection->table('migrations')->insert(['name' => $name]);
        }

        if ($count == 0) {
            $output->writeln('No migrations to run');
        } else if ($count == 1) {
            $output->writeln("{$count} migration run");
        } else {
            $output->writeln("{$count} migrations run");
        }

        $output->writeln("Done");

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
