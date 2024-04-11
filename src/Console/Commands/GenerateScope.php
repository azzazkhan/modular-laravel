<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateScope extends Generator
{
    protected string $type = 'scope';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:scope
                            {name : The name of the scope}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the scope already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new scope class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Models/Scopes');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Models/Scopes', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('scope')->withReplacements($replacements)->publish($path);

        $this->components->info("Scope [$path] created successfully.");
    }
}
