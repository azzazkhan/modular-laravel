<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Support\Str;

class GenerateObserver extends Generator
{
    protected string $type = 'observer';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:observer
                            {name : Name of the observer}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the observer already exists}
                            {--m|model= : The model that the observer applies to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new observer class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Observers');
        $class = str_remove_suffix($class, 'observer');
        [$path, $namespace] = ["$path/{$class}Observer.php", $this->namespace(['Observers', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = 'observer.plain';
        $replacements = ['class' => $class . 'Observer', 'namespace' => $namespace];

        if ($model = $this->option('model')) {
            $stub = 'observer';
            [$model_class, , $model_prefix, $model_variable] = $this->extractClassDetails(str_remove_prefix($model, 'models/'));
            $replacements['model'] = $model_class;
            $replacements['model_name'] = str_replace('-', ' ', Str::kebab($model_class));
            $replacements['model_namespace'] = $this->namespace(['Models', $model_prefix, $model_class]);
            $replacements['model_variable'] = $model_variable;
        }

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Observer [$path] created successfully.");
    }
}
