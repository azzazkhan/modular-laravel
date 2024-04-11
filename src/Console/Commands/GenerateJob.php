<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateJob extends Generator
{
    protected string $type = 'job';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:job
                            {name : Name of the job}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the job already exists}
                            {--s|sync : Indicates that job should be synchronous}
                            {--t|test : Generate an accompanying Pest test for the job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new job class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Jobs');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Jobs', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = $this->isOptionEnabled('sync') ? 'job' : 'job.queued';
        $replacements = ['class' => $class, 'namespace' => $namespace];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Job [$path] created successfully.");

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Jobs/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
