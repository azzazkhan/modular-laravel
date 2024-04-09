<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;

class GenerateView extends Generator
{
    protected string $type = 'view';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:view
                            {name : Name of the view}
                            {--module= : Name of the module}
                            {--extension= : The extension of the generated view [default: "blade.php"]}
                            {--force : Create the view even if the view already exists}
                            {--test : Generate an accompanying Pest test for the view}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $extension = $this->option('extension') ?? 'blade.php';
        $name = preg_replace('/\//', '.', $this->argument('name'));
        $path = array_map(fn (string $segment) => Str::kebab($segment), explode('.', $name));

        $view = last($path);
        $path = count($path) > 1 ? implode('/', array_slice($path, 0, -1)) : '';
        $path = "resources/views" . ($path ? "/$path" : '') . "/$view.$extension";

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $replacements = ['quote' => Inspiring::quotes()->random()];

        $this->makeStub('view')->withReplacements($replacements)->publish($path);

        $this->components->info("View [$path] created successfully.");
    }
}
