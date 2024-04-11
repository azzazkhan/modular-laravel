<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateClass extends Generator
{
    protected string $type = 'class';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:class
                            {name : The name of the class}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the class already exists}
                            {--i|invokable : Generate a single method, invokable class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app');
        [$path, $namespace] = ["$path/$class.php", $this->namespace([$prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = $this->isOptionEnabled('invokable') ? 'class.invokable' : 'class';
        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Class [$path] created successfully.");
    }
}
