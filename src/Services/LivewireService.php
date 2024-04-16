<?php

namespace Azzazkhan\ModularLaravel\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

class LivewireService
{
    public static function registerForModule(string $module): void
    {
        $directory = module_path($module, 'app/Livewire', abs: true);
        $namespace = module_namespace($module, 'Livewire');

        if (!File::isDirectory($directory)) {
            return;
        }

        $components = collect(File::allFiles($directory))
            ->map(function (SplFileInfo $file) use ($namespace) {
                return str_replace(['/', '.php'], ['\\', ''], "$namespace\\" . $file->getRelativePathname());
            })
            ->filter(fn (string $class) => is_subclass_of($class, Component::class) && !(new ReflectionClass($class))->isAbstract())
            ->mapWithKeys(function (string $class) use ($module, $namespace) {
                $alias = Str::kebab($module) . '::' . collect(explode('\\', substr($class, strlen($namespace) + 1)))
                        ->map(fn (string $segment) => Str::kebab($segment))
                        ->join('.');

                return [$class => $alias];
            });

        $components->each(fn (string $alias, string $class) => Livewire::component($alias, $class));
    }
}
