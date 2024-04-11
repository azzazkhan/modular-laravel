<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateFactory extends Generator
{
    protected string $type = 'factory';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:factory
                            {name : Name of the factory}
                            {--module= : Name of the module}
                            {--m|model= : The name of the model}
                            {--f|force= : Create the class even if the factory already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model factory';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'database/Factories');
        $namespace = $this->namespace(['Database\\Factories', $prefix]);

        $class = $this->normalizeFactoryName($class);
        [$model, $model_namespace] = $this->guessModel($class, $prefix);

        $path = "$path/{$class}Factory.php";

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = [
            'class' => $class . 'Factory',
            'namespace' => $namespace,
            'model' => $model,
            'model_namespace' => "$model_namespace\\$model",
        ];

        $stub = $this->option('model') ? 'factory.model' : 'factory';

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);
        $this->components->info("Factory [$path] created successfully.");
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeFactoryName(string $name): string
    {
        if (strlen($name) > 7 && preg_match('/.+(factory)$/i', $name)) {
            return substr($name, 0, -7);
        }

        return $name;
    }

    /**
     * @param string $factory
     * @param string $prefix
     * @return array<string>
     */
    protected function guessModel(string $factory, string $prefix): array
    {
        if ($model = $this->option('model')) {
            $model = preg_match('/^(models\/).+/i', $model) ? substr($model, 7) : $model;

            [$model, , $model_prefix] = $this->extractClassDetails($model);

            return [$model, $this->namespace(['Models', $model_prefix])];
        }

        $model = $this->normalizeFactoryName($factory);
        return [$model, $this->namespace(['Models', $prefix])];
    }
}
