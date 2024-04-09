<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

class GenerateModule extends Generator
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make
                            {name : The name of the module}
                            {--force : Create the module even if the module already exists}
                            {--api : Whether to register API routes for the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new module';

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        if (!$this->shouldForceCreate() && $this->moduleExists()) {
            $this->components->error('Module already exists.');

            return;
        }

        $this->ensureModuleDirectoriesExists();

        $this->publishServiceProviders();
        $this->publishConfig();
        $this->publishComposerJson();
        $this->publishRoutes();
    }

    protected function publishServiceProviders(): void
    {
        $module = $this->moduleName();

        $this->makeStub('service-provider')->withReplacements([
            'namespace' => $this->namespace('Providers'),
            'components_namespace' => $this->namespace('Views\\Components', separator: '\\\\\\\\'),
            'class' => "{$module}ServiceProvider",
        ])->publish($path = "app/Providers/{$module}ServiceProvider.php");

        $this->components->info("Provider [$path] created successfully.");

        $this->makeStub('routes-provider')->withReplacements([
            'namespace' => $this->namespace('Providers'),
            'routes_prefix' => str_plural($this->moduleKey(), '-'),
        ])->publish($path = 'app/Providers/RouteServiceProvider.php');

        $this->components->info("Provider [$path] created successfully.");
    }

    protected function publishConfig(): void
    {
        $this->makeStub('config')->publish($path = 'config/' . $this->moduleKey() . '.php');

        $this->components->info("Config [$path] created successfully.");
    }

    protected function publishComposerJson(): void
    {
        $replacements = [
            'module_namespace' => $this->namespace(separator: '\\\\\\\\'),
        ];

        $this->makeStub('composer')->withReplacements($replacements)->publish('composer.json');

        $this->components->info('File [composer.json] created successfully.');
    }

    protected function publishRoutes(): void
    {
        $this->makeStub('web')->publish($path = 'routes/web.php');
        $this->components->info("Routes [$path] created successfully.");

        if ($this->isOptionEnabled('api')) {
            $this->makeStub('api')->publish($path = 'routes/api.php');
            $this->components->info("Routes [$path] created successfully.");
        }
    }

    protected function module(): string
    {
        return $this->argument('name');
    }
}
