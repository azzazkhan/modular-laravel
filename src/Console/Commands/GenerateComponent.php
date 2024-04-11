<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Str;

class GenerateComponent extends Generator
{
    protected string $type = 'component';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:component
                            {name : Name of the component}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the component already exists}
                            {--inline :  Create a component that renders an inline view}
                            {--v|view : Create an anonymous component with only a view}
                            {--t|test : Generate an accompanying Pest test for the component}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new view component class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = explode('/', str_replace('.', '/', $this->argument('name')));
        $name = collect($name)->filter()->map(fn(string $segment) => Str::studly($segment))->join('/');

        [$class, $path, $prefix] = $this->extractClassDetails($name, 'app/View/Components');
        [$component_path, $namespace] = ["$path/$class.php", $this->namespace(['View\\Components', $prefix])];

        $view_path = explode('/', ltrim("$prefix/$class", '/'));
        $view_path = array_map(fn(string $segment) => ltrim(rtrim($segment, '/'), '/'), $view_path);
        $view_path = array_filter($view_path, fn(string $segment) => strlen($segment) > 0);
        $view_path = array_map(fn(string $segment) => Str::kebab($segment), $view_path);
        $view_path = 'components/' . implode('/', $view_path);
        $view_name = str_replace('/', '.', $view_path);
        $view_path = "resources/views/$view_path.blade.php";

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($component_path) || !$this->validateFileAbsence($view_path)) {
            return;
        }

        $inline = $this->isOptionEnabled('inline');
        $view = $this->isOptionEnabled('view');

        if ($inline && $view) {
            $this->components->error('Inline and view options cannot be used together!');

            return;
        }

        $replacements = [
            'namespace' => $namespace,
            'class' => $class,
            'view' => sprintf("view('%s::%s')", $this->moduleKey(), $view_name),
        ];

        if ($inline) {
            $i = str_repeat(' ', 4);
            $bi = str_repeat($i, 2);
            $ci = str_repeat($i, 3);

            $quote = sprintf('<!-- %s -->', Inspiring::quotes()->random());

            $replacements['view'] = "<<<BLADE\n$ci<div>\n$ci$i$quote\n$ci</div>\n$bi" . 'BLADE';
        }

        if (!$view) {
            $this->makeStub('view-component')->withReplacements($replacements)->publish($component_path);
        }

        if (!$inline) {
            $this->components->info("Component [$component_path] created successfully.");
        }

        $this->call('module:view', ['name' => $view_name, '--module' => $this->option('module'), '--force' => true]);

        if ($this->isOptionEnabled('test')) {
            $this->call(GenerateTest::class, [
                'name' => 'View/Components/' . ltrim("$prefix/$class", '/'),
                '--module' => $this->option('module'),
                '--force' => $this->shouldForceCreate(),
            ]);
        }
    }
}
