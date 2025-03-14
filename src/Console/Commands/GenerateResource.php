<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateResource extends Generator
{
    protected string $type = 'resource';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:resource
                            {name : Name of the resource}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the resource already exists}
                            {--c|collection : Create a resource collection}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new resource';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Http/Resources');
        $class = str_remove_suffix($class, 'resource') . 'Resource';
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http/Resources', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = $this->isOptionEnabled('collection') ? 'resource-collection' : 'resource';
        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Resource [$path] created successfully.");
    }
}
