<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateRequest extends Generator
{
    protected string $type = 'request';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:request
                            {name : The name of the request}
                            {--module= : Name of the module}
                            {--force : Create the class even if the request already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form request class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Http/Requests');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http\\Requests', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('request')->withReplacements($replacements)->publish($path);

        $this->components->info("Class [$path] created successfully.");
    }
}
