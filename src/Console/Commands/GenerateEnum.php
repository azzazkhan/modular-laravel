<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateEnum extends Generator
{
    protected string $type = 'enum';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:enum
                            {name : Name of the enum}
                            {--module= : Name of the module}
                            {--force : Create the enum even if the enum already exists}
                            {--string : Generate a string backed enum}
                            {--int : Generate an integer backed enum}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new enum';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Enums');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Enums', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        [$stub, $type] = match (true) {
            $this->isOptionEnabled('string') => ['enum.backed', 'string'],
            $this->isOptionEnabled('int') => ['enum.backed', 'int'],
            default => ['enum', null],
        };

        $replacements = [
            'class' => $class,
            'namespace' => $namespace,
            'type' => $type,
        ];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Enum [$path] created successfully.");
    }
}
