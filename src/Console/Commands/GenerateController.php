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
                            {--f|force : Create the class even if the controller already exists}
                            {--api : Create a new controller for the model}
                            {--i|invokable :  Generate a single method, invokable controller class}
                            {--r|requests : Generate FormRequest classes for store and update}
                            {--t|test : Generate an accompanying Pest test for the controller}';

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
        $class = str_remove_suffix($class, 'controller') . 'Controller';
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

        if ($this->isOptionEnabled('requests')) {
            $name = ltrim("$prefix/$class", '/');

            $this->call(GenerateRequest::class, [
                'name' => $name . '/StoreRequest',
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);

            $this->call(GenerateRequest::class, [
                'name' => $name . '/UpdateRequest',
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Http/Controllers/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
