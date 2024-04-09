<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateMigration extends Generator
{
    protected string $type = 'migration';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:migration
                            {name : Name of the migration}
                            {--module= : Name of the module}
                            {--create : The table to be created}
                            {--table : The table to migrate}
                            {--path : The location where the migration file should be created}
                            {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (!$this->validateModuleExistence()) {
            return;
        }

        $this->call('make:migration', [
            'name' => $this->argument('name'),
            '--create' => $this->option('create'),
            '--table' => $this->option('table'),
            '--path' => $this->path('database/migrations', abs: false) . trim($this->option('path'), '/'),
            '--realpath' => $this->option('realpath'),
        ]);
    }
}
