<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateTest extends Generator
{
    protected string $type = 'test';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:test
                            {name : Name of the test}
                            {--module= : Name of the module}
                            {--f|force : Create the test even if the test already exists}
                            {--u|unit : Create a unit test}
                            {--pest : Create a Pest test}
                            {--phpunit : Create a PHPUnit test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new test class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $unit = $this->isOptionEnabled('unit');
        $path = $unit ? 'tests/Unit' : 'tests/Feature';
        $namespace = $unit ? 'Tests/Unit' : 'Tests/Feature';
        $name = str_remove_suffix($this->argument('name'), 'test');

        [$class, $path, $prefix] = $this->extractClassDetails($name, $path);
        [$path, $namespace] = ["$path/{$class}Test.php", $this->namespace([$namespace, $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class . 'Test', 'namespace' => $namespace];

        $stub = match (true) {
            $this->option('phpunit') && $unit => 'test.unit',
            $this->option('phpunit') => 'test',
            $this->option('pest') && $unit => 'pest.unit',
            default => 'pest',
        };

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Test [$path] created successfully.");
    }
}
