<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use Azzazkhan\ModularLaravel\Concerns\InteractsWithFiles;
use Azzazkhan\ModularLaravel\Factories\Stub;
use Azzazkhan\ModularLaravel\Factories\StubModule;
use Azzazkhan\ModularLaravel\Providers\ModuleServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class Generator extends Command
{
    use InteractsWithFiles;

    protected static string $rootNamespace;

    protected static string $rootDirectory;

    protected string $type = 'class';

    public function __construct()
    {
        parent::__construct();

        static::$rootNamespace ??= ModuleServiceProvider::NAMESPACE;
        static::$rootDirectory ??= Str::kebab(static::$rootNamespace);
    }

    /**
     * Check if `force` option is enabled.
     *
     * @return bool
     */
    protected function shouldForceCreate(): bool
    {
        return $this->isOptionEnabled('force');
    }

    /**
     * Check if the provided option is enabled.
     *
     * @param string $name
     * @return bool
     */
    protected function isOptionEnabled(string $name): bool
    {
        $values = [true, 'true', 'yes', '1'];
        return $this->hasOption($name) && in_array($this->option($name), $values);
    }

    /**
     * Displays error if module does not exists.
     *
     * @return bool
     */
    protected function validateModuleExistence(): bool
    {
        if (!$this->module()) {
            $this->components->error('Module name is required!');

            return false;
        }

        if (!$this->moduleExists()) {
            $this->components->error("The module [{$this->moduleName()}] does not exist!");

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function module(): string
    {
        return $this->option('module') ?? '';
    }

    /**
     * Check if the module exists.
     *
     * @return bool
     */
    protected function moduleExists(): bool
    {
        $module = $this->moduleName();

        return $this->exists("app/Providers/{$module}ServiceProvider.php") || $this->exists('composer.json');
    }

    /**
     * @return string
     */
    protected function moduleName(): string
    {
        return Str::studly($this->module());
    }

    /**
     * @param string $name
     * @return \Azzazkhan\ModularLaravel\Factories\Stub
     */
    protected function makeStub(string $name): Stub
    {
        $module = new StubModule($this->moduleName(), $this->moduleKey(), $this->namespace(), $this->modulePath());

        return Stub::make($name)->forModule($module);
    }

    /**
     * @return string
     */
    public function moduleKey(): string
    {
        return Str::kebab($this->module());
    }

    /**
     * @param string|array|null $append
     * @param string|null $separator
     * @return string
     */
    protected function namespace(string|array $append = null, string $separator = null): string
    {
        if (is_array($append)) {
            $append = collect($append)
                ->filter(fn(string $chunk) => strlen($chunk))
                ->map(fn(string $chunk) => trim(str_replace('/', '\\', $chunk), '\\'))
                ->join('\\');
        }

        $namespace = static::$rootNamespace . '\\\\' . $this->moduleName();
        $namespace = $append ? $namespace . '\\\\' . $append : $namespace;

        return $separator ? str_replace('\\\\', $separator, $namespace) : $namespace;
    }
}
