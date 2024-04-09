<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateEvent extends Generator
{
    protected string $type = 'event';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:event
                            {name : Name of the event}
                            {--module= : Name of the module}
                            {--force : Create the class even if the event already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new event class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Events');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Events', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('event')->withReplacements($replacements)->publish($path);

        $this->components->info("Event [$path] created successfully.");
    }
}
