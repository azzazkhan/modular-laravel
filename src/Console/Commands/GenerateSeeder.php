<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateSeeder extends Generator
{
    protected string $type = 'seeder';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:seeder
                            {name : The name of the seeder}
                            {--module= : Name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'database/Seeders');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Database\\Seeders', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('seeder')->withReplacements($replacements)->publish($path);

        $this->components->info("Seeder [$path] created successfully.");
    }
}
