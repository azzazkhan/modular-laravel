<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Support\Str;

class GenerateMail extends Generator
{
    protected string $type = 'mailable';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:mail
                            {name : Name of the mailable}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the mailable already exists}
                            {--m|markdown : Create a new markdown template for the mailable}
                            {--t|test : Generate an accompanying Pest test for the Mailable}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new mailable class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Mail');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Mail', $prefix])];
        $view_path = array_map(fn(string $s) => Str::kebab($s), explode('/', substr($path, 9, -4)));
        $view_name = $this->moduleKey() . '::mail.' . implode('.', $view_path);
        $view_path = 'resources/views/mail/' . implode('/', $view_path) . '.blade.php';

        $markdown = $this->isOptionEnabled('markdown');

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path) || ($markdown && !$this->validateFileAbsence($view_path, 'markdown'))) {
            return;
        }

        $stub = $markdown ? 'markdown-mail' : 'mail';
        $replacements = ['class' => $class, 'namespace' => $namespace, 'subject' => Str::headline($class), 'view' => $view_name];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Mailable [$path] created successfully.");

        if ($markdown) {
            $this->makeStub('markdown')->publish($view_path);

            $this->components->info("Markdown [$view_path] created successfully.");
        }

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'Mail/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
