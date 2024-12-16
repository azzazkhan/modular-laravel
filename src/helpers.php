<?php

use Azzazkhan\ModularLaravel\Providers\ModuleServiceProvider;
use Illuminate\Support\Str;

if (!function_exists('filterBooleanArray')) {
    /**
     * Filters out falsy values for associative entries.
     *
     * @param array $array
     * @return array
     */
    function filter_bool_array(array $array): array
    {
        $filtered = [];

        foreach ($array as $key => $value) {
            if (is_string($key) && !!$value) {
                $filtered[] = $key;
            } else {
                if (is_int($key)) {
                    $filtered[] = $value;
                }
            }
        }

        return $filtered;
    }
}

if (!function_exists('str_plural')) {
    /**
     * Pluralizes the last word in given string.
     *
     * @param string $string
     * @param string|null $separator
     * @return string
     */
    function str_plural(string $string, ?string $separator = ' '): string
    {
        if (strlen($string) > 0) {
            if ($separator) {
                $segments = explode($separator, $string);
                $plural = Str::plural(last($segments));

                if (count($segments) == 0) {
                    return $plural;
                }

                return implode($separator, [...array_slice($segments, 0, -1), $plural]);
            }
        }

        return $string;
    }
}

if (!function_exists('str_remove_prefix')) {
    /**
     * Removes specified prefix from start the provided string.
     *
     * @param string $string
     * @param string $prefix
     * @param bool $strict
     * @return string
     */
    function str_remove_prefix(string $string, string $prefix, bool $strict = false): string
    {
        $prefix = $strict ? strtolower($prefix) : $prefix;
        $flags = $strict ? '' : 'i';
        $search = str_replace(['/'], ['\/'], $prefix);

        return preg_match("/^($search).*/$flags", $string) ? substr($string, strlen($prefix)) : $string;
    }
}

if (!function_exists('str_remove_suffix')) {
    /**
     * Removes specified suffix from end the provided string.
     *
     * @param string $string
     * @param string $suffix
     * @param bool $strict
     * @return string
     */
    function str_remove_suffix(string $string, string $suffix, bool $strict = false): string
    {
        $suffix = $strict ? strtolower($suffix) : $suffix;
        $flags = $strict ? '' : 'i';
        $search = str_replace(['/'], ['\/'], $suffix);

        return preg_match(sprintf('/.*(%s)$/%s', $search, $flags), $string) ? substr($string, 0, -strlen($suffix)) : $string;
    }
}

if (!function_exists('module_path')) {
    function module_path(string $module, string $append = '', bool $abs = false): string
    {
        $base = Str::kebab(ModuleServiceProvider::NAMESPACE);
        $module = Str::studly($module);

        $path = app()->joinPaths("$base/$module", $append);

        return $abs ? base_path($path) : $path;
    }
}

if (!function_exists('module_namespace')) {
    function module_namespace(string $module, array|string $append = null): string
    {
        $namespace = [ModuleServiceProvider::NAMESPACE, Str::studly($module)];
        $append = str_replace('/', '\\', $append);
        $append = preg_replace('/(\\\\){2,}/', '\\', $append);

        $namespace = array_merge($namespace, explode('\\', $append));
        $namespace = array_filter($namespace, fn(string $segment) => !!$segment);

        return implode('\\', $namespace);
    }
}
