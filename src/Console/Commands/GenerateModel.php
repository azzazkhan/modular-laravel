<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Support\Str;

class GenerateModel extends Generator
{
    protected string $type = 'model';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:model
                            {name : Name of the Eloquent model}
                            {--module= : Name of the module}
                            {--force : Create the class even if the model already exists}
                            {--c|controller : Create a new controller for the model}
                            {--f|factory : Create a new factory for the model}
                            {--m|migration : Create a new migration file for the model}
                            {--morph-pivot : Indicates if the generated model should be a custom polymorphic intermediate table model}
                            {--o|observer : Create a new observer for the model}
                            {--p|policy : Create a new policy for the model}
                            {--s|seed : Create a new seeder for the model}
                            {--pivot : Indicates if the generated model should be a custom intermediate table model}
                            {--api : Indicates if the generated controller should be an API controller}
                            {--t|test : Generate an accompanying Pest test for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Models');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Models', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = match (true) {
            $this->isOptionEnabled('morph-pivot') => 'model.morph-pivot',
            $this->isOptionEnabled('pivot') => 'model.pivot',
            $this->isOptionEnabled('factory') => 'model.factory',
            default => 'model',
        };

        $replacements = [
            'class' => $class,
            'namespace' => $namespace,
            'factory_class' => $factoryName = $class . 'Factory',
            'factory_fqn' => $this->namespace(['Database\\Factories', $prefix, $factoryName]),
        ];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Model [$path] created successfully.");

        $name = ltrim("$prefix/$class", '/');

        if ($this->isOptionEnabled('controller')) {
            $this->call(GenerateController::class, [
                'name' => $name,
                '--module' => $this->module(),
                '--api' => $this->isOptionEnabled('api'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }

        if ($this->isOptionEnabled('factory')) {
            $this->call(GenerateFactory::class, [
                'name' => ltrim("$prefix/$factoryName"),
                '--module' => $this->module(),
                '--model' => $name,
                '--force' => $this->shouldForceCreate(),
            ]);
        }

        if ($this->isOptionEnabled('migration')) {
            $this->call(GenerateMigration::class, [
                'name' => 'create_' . ($table = str_plural(Str::snake($class), '_')) . '_table',
                '--module' => $this->module(),
                '--create' => $table,
            ]);
        }

        if ($this->isOptionEnabled('observer')) {
            $this->call(GenerateObserver::class, [
                'name' => $name,
                '--module' => $this->module(),
                '--model' => $name,
                '--force' => $this->shouldForceCreate(),
            ]);
        }

        if ($this->isOptionEnabled('policy')) {
            $this->call(GeneratePolicy::class, [
                'name' => $name,
                '--module' => $this->module(),
                '--model' => $name,
                '--force' => $this->shouldForceCreate(),
            ]);
        }

        if ($this->isOptionEnabled('seed')) {
            $this->call(GenerateSeeder::class, [
                'name' => $class,
                '--module' => $this->module(),
            ]);
        }

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Models/' . $name,
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
