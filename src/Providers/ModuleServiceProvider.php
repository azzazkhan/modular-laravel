<?php

namespace Azzazkhan\ModularLaravel\Providers;

use Azzazkhan\ModularLaravel\Services\RoutingService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    const string NAMESPACE = 'Modules';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RoutingService::class);

        if ($this->app->runningInConsole()) {
            /** @var array<class-string> $commands */
            $commands = collect(File::glob(__DIR__ . '/../Console/Commands/*.php'))
                ->map(fn(string $path) => substr(last(explode('/', $path)), 0, -4))
                ->filter(fn(string $class) => $class != 'Generator')
                ->map(fn(string $class) => "Azzazkhan\\ModularLaravel\\Console\\Commands\\$class")
                ->toArray();

            $this->commands($commands);
        }

        $namespace = self::NAMESPACE;
        $filepath = base_path('modules/{name}/app/Providers/{name}ServiceProvider.php');
        $namespace = "$namespace\\{name}\\Providers\\{name}ServiceProvider";

        collect(File::glob(base_path('modules/*')))
            ->map(fn(string $path) => last(explode('/', $path)))
            ->filter(fn(string $name) => File::exists(str_replace('{name}', $name, $filepath)))
            ->map(fn(string $name) => str_replace('{name}', $name, $namespace))
            ->each(fn(string $provider) => $this->app->register($provider));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
