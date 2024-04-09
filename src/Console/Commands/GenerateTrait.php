<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateTrait extends Generator
{
    protected string $type = 'trait';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:trait
                            {name : Name of the trait}
                            {--module= : Name of the module}
                            {--force : Create the trait even if the trait already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new trait';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Traits');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Traits', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('trait')->withReplacements($replacements)->publish($path);

        $this->components->info("Trait [$path] created successfully.");
    }
}
