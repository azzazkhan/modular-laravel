<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Support\Str;

class GenerateConsole extends Generator
{
    protected string $type = 'console command';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:command
                            {name : The name of the command}
                            {--module= : Name of the module}
                            {--force : Create the class even if the console command already exists}
                            {--command= : The terminal command that will be used to invoke the class}
                            {--test : Generate an accompanying Pest test for the Console command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Artisan command';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Console/Commands');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Console\\Commands', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $module = $this->moduleKey();

        if (($command = $this->option('command'))) {
            $command = str_contains($command, ':') ? $command : "$module:$command";
        }
        else {
            $command = "$module:" . Str::kebab($class);
        }

        $replacements = [
            'class' => $class,
            'namespace' => $namespace,
            'command' => $command,
        ];

        $this->makeStub('console')->withReplacements($replacements)->publish($path);

        $this->components->info("Console command [$path] created successfully.");

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Console/Commands/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
