<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateConcern extends Generator
{
    protected string $type = 'concern';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:concern
                            {name : Name of the concern}
                            {--module= : Name of the module}
                            {--e|eloquent : Indicates if the concern is for an Eloquent model}
                            {--f|force : Create the trait even if the concern already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new concern';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $eloquent = $this->isOptionEnabled('eloquent');
        $path = $eloquent ? 'app/Concerns/Eloquent' : 'app/Concerns';
        $namespace = $eloquent ? 'Concerns/Eloquent' : 'Concerns';

        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), $path);
        [$path, $namespace] = ["$path/$class.php", $this->namespace([$namespace, $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('trait')->withReplacements($replacements)->publish($path);

        $this->components->info("Concern [$path] created successfully.");
    }
}
