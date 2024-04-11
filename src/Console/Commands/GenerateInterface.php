<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateInterface extends Generator
{
    protected string $type = 'interface';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:interface
                            {name : Name of the interface}
                            {--module= : Name of the module}
                            {--f|force : Create the interface even if the interface already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new interface';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Interfaces');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Interfaces', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('interface')->withReplacements($replacements)->publish($path);

        $this->components->info("Interface [$path] created successfully.");
    }
}
