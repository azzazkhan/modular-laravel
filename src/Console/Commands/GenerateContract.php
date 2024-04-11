<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateContract extends Generator
{
    protected string $type = 'contract';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:contract
                            {name : Name of the contract}
                            {--module= : Name of the module}
                            {--f|force : Create the interface even if the contract already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new contract';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Contracts');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Contracts', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('interface')->withReplacements($replacements)->publish($path);

        $this->components->info("Contract [$path] created successfully.");
    }
}
