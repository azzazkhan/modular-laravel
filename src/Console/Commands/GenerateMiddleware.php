<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateMiddleware extends Generator
{
    protected string $type = 'middleware';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:middleware
                            {name : Name of the middleware}
                            {--module= : Name of the module}
                            {--force : Create the class even if the middleware already exists}
                            {--test : Generate an accompanying Pest test for the middleware}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new middleware class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Http/Middlewares');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http/Middlewares', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('middleware')->withReplacements($replacements)->publish($path);

        $this->components->info("Middleware [$path] created successfully.");
    }
}
