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
                            {--force : Create the class even if the job already exists}
                            {--sync : Indicates that job should be synchronous}
                            {--test : Generate an accompanying Pest test for the job}';

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
    }
}
