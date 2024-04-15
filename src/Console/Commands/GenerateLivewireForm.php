<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateLivewireForm extends Generator
{
    protected string $type = 'livewire form';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:livewire-form
                            {name : Name of the form}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the form already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire form class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Livewire/Forms');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Livewire/Forms', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('livewire.form')->withReplacements($replacements)->publish($path);

        $this->components->info("Livewire form [$path] created successfully.");
    }
}