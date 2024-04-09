<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateServiceProvider extends Generator
{
    protected string $type = 'provider';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:provider
                            {name : The name of the provider}
                            {--module= : Name of the module}
                            {--force : Create the class even if the provider already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service provider class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Providers');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Providers', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('provider')->withReplacements($replacements)->publish($path);

        $this->components->info("Provider [$path] created successfully.");
    }
}
