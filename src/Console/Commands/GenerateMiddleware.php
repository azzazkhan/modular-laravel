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
                            {--f|force : Create the class even if the middleware already exists}
                            {--t|test : Generate an accompanying Pest test for the middleware}';

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
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Http/Middleware');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Http/Middleware', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub('middleware')->withReplacements($replacements)->publish($path);

        $this->components->info("Middleware [$path] created successfully.");

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Http/Middleware/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
