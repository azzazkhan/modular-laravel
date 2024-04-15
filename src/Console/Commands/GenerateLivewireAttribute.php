<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateLivewireAttribute extends Generator
{
    protected string $type = 'livewire attribute';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:livewire-attribute
                            {name : Name of the attribute}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the attribute already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire attribute class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Livewire/Attributes');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Livewire/Attributes', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('livewire.attribute')->withReplacements($replacements)->publish($path);

        $this->components->info("Livewire attribute [$path] created successfully.");
    }
}