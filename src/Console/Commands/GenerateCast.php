<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateCast extends Generator
{
    protected string $type = 'cast';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:cast
                            {name : Name of the cast}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the cast already exists}
                            {--inbound : Generate an inbound cast class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom Eloquent cast class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Casts');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Casts', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = $this->isOptionEnabled('inbound') ? 'cast.inbound' : 'cast';
        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Cast [$path] created successfully.");
    }
}
