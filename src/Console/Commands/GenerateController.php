<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateController extends Generator
{
    protected string $type = 'controller';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:controller
                            {name : Name of the Eloquent model}
                            {--module= : Name of the module}
                            {--force : Create the class even if the controller already exists}
                            {--api : Create a new controller for the model}
                            {--invokable :  Generate a single method, invokable controller class}
                            {--requests : Generate FormRequest classes for store and update}
                            {--test : Generate an accompanying Pest test for the controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new controller class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Http/Controllers');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http/Controllers', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = match (true) {
            $this->isOptionEnabled('api') => 'controller.api',
            $this->isOptionEnabled('invokable') => 'controller.invokable',
            default => 'controller',
        };

        if (!$this->exists('app/Http/Controllers/Controller.php')) {
            $this->makeStub('controller.base')->publish('app/Http/Controllers/Controller.php');

            $this->components->info("Base controller [app/Http/Controllers/Controller.php] created successfully.");
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Controller [$path] created successfully.");
    }
}
