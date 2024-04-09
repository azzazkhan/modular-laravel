<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateListener extends Generator
{
    protected string $type = 'listener';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:listener
                            {name : Name of the listener}
                            {--module= : Name of the module}
                            {--force : Create the class even if the listener already exists}
                            {--event= : The event class being listened for}
                            {--queued : Indicates the event listener should be queued}
                            {--test : Generate an accompanying Pest test for the listener}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new event listener class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Listeners');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Listeners', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        if ($event = $this->option('event')) {
            $stub = $this->isOptionEnabled('queued') ? 'listener.typed.queued' : 'listener.typed';
            [$event, , $prefix] = $this->extractClassDetails($event);
            $replacements['event_namespace'] = $this->namespace(['Events', $prefix, $event]);
            $replacements['event'] = $event;
        }
        else {
            $stub = $this->isOptionEnabled('queued') ? 'listener.queued' : 'listener';
        }

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Listener [$path] created successfully.");
    }
}
