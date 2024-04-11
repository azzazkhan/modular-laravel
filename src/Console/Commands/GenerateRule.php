<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateRule extends Generator
{
    protected string $type = 'rule';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:rule
                            {name : The name of the rule}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the rule already exists}
                            {--i|implicit : Generate an implicit rule}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new validation rule';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Rules');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Rules', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = $this->isOptionEnabled('implicit') ? 'rule.implicit' : 'rule';
        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Rule [$path] created successfully.");
    }
}
