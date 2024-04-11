<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateException extends Generator
{
    protected string $type = 'exception';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:exception
                            {name : Name of the exception}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the exception already exists}
                            {--render : Create the exception with an empty render method}
                            {--report : Create the exception with an empty report method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom exception class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Exceptions');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Exceptions', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }


        $stub = match (true) {
            $this->isOptionEnabled('render') && $this->isOptionEnabled('report') => 'exception-render-report',
            $this->isOptionEnabled('render') => 'exception-render',
            $this->isOptionEnabled('report') => 'exception-report',
            default => 'exception',
        };

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Enum [$path] created successfully.");
    }
}
