<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Illuminate\Support\Str;

class GenerateNotification extends Generator
{
    protected string $type = 'notification';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:notification
                            {name : Name of the notification}
                            {--module= : Name of the module}
                            {--force : Create the class even if the notification already exists}
                            {--markdown : Create a new markdown template for the notification}
                            {--test : Generate an accompanying Pest test for the notification}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new notification class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Notifications');
        [$path, $namespace] = ["$path/$class.php", $this->namespace(['Notifications', $prefix])];
        $view_path = array_map(fn (string $s) => Str::kebab($s), explode('/', substr($path, 18, -4)));
        $view_name = $this->moduleKey() . '::notifications.' . implode('.', $view_path);
        $view_path = 'resources/views/notifications/' . implode('/', $view_path) . '.blade.php';

        $markdown = $this->isOptionEnabled('markdown');

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path) || ($markdown && !$this->validateFileAbsence($view_path, 'markdown'))) {
            return;
        }

        $stub = $markdown ? 'markdown-notification' : 'notification';
        $replacements = ['class' => $class, 'namespace' => $namespace, 'subject' => Str::headline($class), 'view' => $view_name];

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Notification [$path] created successfully.");

        if ($markdown) {
            $this->makeStub('markdown')->publish($view_path);

            $this->components->info("Markdown [$view_path] created successfully.");
        }
    }
}
