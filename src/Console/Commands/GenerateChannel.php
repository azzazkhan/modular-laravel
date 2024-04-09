<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateChannel extends Generator
{
    protected string $type = 'channel';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:channel
                            {name : Name of the channel}
                            {--module= : Name of the module}
                            {--force : Create the class even if the channel already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new channel class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Broadcasting');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Broadcasting', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('channel')->withReplacements($replacements)->publish($path);

        $this->components->info("Channel [$path] created successfully.");
    }
}
