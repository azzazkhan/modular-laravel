<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;

class GenerateLivewireComponent extends Generator
{
    protected string $type = 'livewire component';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:livewire
                            {name : Name of the Livewire component}
                            {--module= : Name of the module}
                            {--i|inline : Create a component that renders an inline view}
                            {--f|force : Create the class even if the component already exists}
                            {--t|test : Generate an accompanying Pest test for the component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Livewire component';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = explode('/', str_replace('.', '/', $this->argument('name')));
        $name = collect($name)->filter()->map(fn (string $segment) => Str::studly($segment))->join('/');

        [$class, $path, $prefix] = $this->extractClassDetails($name, 'app/Livewire');
        [$component_path, $namespace] = ["$path/$class.php", $this->namespace(['Livewire', $prefix])];


        $view_path = collect(explode('/', ltrim("$prefix/$class", '/')))
            ->map(fn (string $segment) => ltrim(rtrim($segment, '/'), '/'))
            ->filter(fn (string $segment) => strlen($segment) > 0)
            ->map(fn (string $segment) => Str::kebab($segment))
            ->toArray();

        $view_path = 'livewire/' . implode('/', $view_path);
        $view_name = str_replace('/', '.', $view_path);
        $view_path = "resources/livewire/$view_path.blade.php";

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($component_path) || !$this->validateFileAbsence($view_path)) {
            return;
        }

        $inline = $this->isOptionEnabled('inline');

        $replacements = [
            'namespace' => $namespace,
            'class' => $class,
            'view' => $this->moduleKey() . '::' . $view_name,
            'quote' => Inspiring::quotes()->random(),
        ];

        $stub = $inline ? 'livewire.inline' : 'livewire';
        $this->makeStub($stub)->withReplacements($replacements)->publish($component_path);
        $this->components->info("Component [$component_path] created successfully.");


        if (!$inline) {
            $this->call(GenerateView::class, ['name' => $view_name, '--module' => $this->option('module'), '--force' => true]);
        }

        $this->components->info(sprintf('Tag <livewire:%s::%s />', $this->moduleKey(), substr($view_name, 9)));


        if ($this->isOptionEnabled('test')) {
            $replacements = ['class' => $class, 'namespace' => "$namespace\\$class"];

            $path = 'tests/Feature/Livewire/' . ltrim("$prefix/{$class}Test.php", '/');
            $this->makeStub('livewire.pest')->withReplacements($replacements)->publish($path);

            $this->components->info("Test [$path] created successfully.");
        }
    }
}
