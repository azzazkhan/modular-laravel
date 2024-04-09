<?php

namespace Azzazkhan\ModularLaravel\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * @method string module()
 * @method bool shouldForceCreate()
 */
trait InteractsWithFiles
{
    /**
     * @return void
     */
    protected function ensureModuleDirectoriesExists(): void
    {
        $this->ensureDirectoryExists('config');
        $this->ensureDirectoryExists('app/Providers');
    }

    /**
     * @param  string  $path
     * @return void
     */
    protected function ensureDirectoryExists(string $path): void
    {
        File::ensureDirectoryExists($this->path($path));
    }

    /**
     * Returns the full path to file in module directory (if provided).
     *
     * @param  string|null  $path
     * @param  bool  $abs
     * @return string
     */
    protected function path(string $path = null, bool $abs = true): string
    {
        $path = $this->modulePath() . ($path ? '/' . $path : '');

        return $abs ? base_path($path) : $path;
    }

    /**
     * Get the base path to module (relative to project).
     *
     * @return string
     */
    protected function modulePath(): string
    {
        return static::$rootDirectory . '/' . $this->moduleDirectoryName();
    }

    /**
     * Get the name of the module directory.
     *
     * @return string
     */
    protected function moduleDirectoryName(): string
    {
        return Str::studly($this->module());
    }

    /**
     * @param  string  $classname
     * @param  string|null  $basePath
     * @return array
     */
    protected function extractClassDetails(string $classname, string $basePath = null): array
    {
        $segments = explode('/', str_replace('\\', '', $classname));
        $classname = Str::studly(last($segments));
        $prefix = implode('/', array_slice($segments, 0, count($segments) - 1));
        $path = rtrim("$basePath/$prefix", '/');
        $variable = Str::camel($classname);

        return [$classname, $path, $prefix, $variable];
    }

    /**
     * Outputs error if specified file already exists and force option is
     * disabled.
     *
     * @param  string  $path
     * @param  string|null  $type
     * @return bool
     */
    protected function validateFileAbsence(string $path, string $type = null): bool
    {
        if (!$this->shouldForceCreate() && $this->exists($path)) {
            $this->components->error(ucfirst($type ?? $this->type) . ' already exists.');

            return false;
        }

        return true;
    }

    /**
     * Check if the given file exists in module path.
     *
     * @param  string  $path
     * @return bool
     */
    protected function exists(string $path): bool
    {
        return File::exists($this->path($path));
    }
}
