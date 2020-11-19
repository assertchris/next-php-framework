<?php

namespace Next\Commands;

use Next\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';

    protected function configure()
    {
        // TODO
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!Database::schema()->hasTable('migrations')) {
            Database::schema()->create('migrations', function($table) {
                $table->string('name')->unique();
            });
        }
        
        $path = path('migrations');
        $migrations = files("{$path}/*.php");
        
        foreach ($migrations as $migration) {
            $name = basename($migration, '.php');
        
            if (Database::table('migrations')->where('name', $name)->first()) {
                continue;
            }
        
            $runner = require $migration;
            $runner();
        
            Database::table('migrations')->insert(['name' => $name]);
        }

        return Command::SUCCESS;
    }
}
