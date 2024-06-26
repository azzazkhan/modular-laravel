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
                            {--f|force : Create the class even if the request already exists}';

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
        $name = str_remove_suffix($class, 'request') . 'Request';
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http\\Requests', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('request')->withReplacements($replacements)->publish($path);

        $this->components->info("Request [$path] created successfully.");
    }
}
