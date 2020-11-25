<?php

namespace Next\Database\Commands;

class MigrateCommand extends \Next\Console\Command
{
    protected string $signature = 'migrate';

    protected string $description = 'Migrate the database';

    public function handle(\Next\Database $connection) {
        $count = 0;
        $this->line('Migrating');

        if (!$connection->schema()->hasTable('migrations')) {
            $this->info('No migrations table found.');
            $this->line('Creating migrations table');

            $connection->schema()->create('migrations', function ($table) {
                $table->string('name')->unique();
            });
        }

        $path = path('migrations');
        $migrations = files($path);

        foreach ($migrations as $migration) {
            $name = basename($migration, '.php');

            if (
                $connection
                    ->table('migrations')
                    ->where('name', $name)
                    ->first()
            ) {
                continue;
            }

            $count++;
            $this->info("Migrating {$name}");

            $runner = require $migration;
            $runner($connection);

            $connection->table('migrations')->insert(['name' => $name]);
        }

        if ($count == 0) {
            $this->line('No migrations to run');
        } elseif ($count == 1) {
            $this->line("{$count} migration run");
        } else {
            $this->line("{$count} migrations run");
        }

        $this->line('Done');

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
