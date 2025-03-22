<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupProject extends Command
{
    protected $signature = 'app:setup {path?}';
    protected $description = 'Runs multiple commands in order to set up the project';

    public function handle()
    {
        $this->info('Running migrations and seeding...');
        $this->call('migrate:fresh', ['--seed' => true]);

        $path = $this->argument('path') ?? '~/Downloads/Websites.xlsx';

        $this->info("Importing websites from {$path}...");
        $this->call('import:websites', ['file' => $path]);

        $this->info('Finding hosting for websites...');
        $this->call('app:find-hosting');

        $this->info('Checking websites...');
        $this->call('websites:check');

        $this->info('Setup completed successfully!');
    }
}
