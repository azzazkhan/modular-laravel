<?php

namespace Azzazkhan\ModularLaravel\Factories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Stub
{
    const string REPLACEMENT_ARRAY_SEPARATOR = '';

    /**
     * The name of stub-file (without extension).
     *
     * @var string
     */
    protected string $stub;

    /**
     * The module for current stub.
     *
     * @var \Azzazkhan\ModularLaravel\Factories\StubModule|null
     */
    protected ?StubModule $module = null;

    /**
     * The keys and values for stub replacements.
     *
     * @var array<string, mixed>
     */
    protected array $replacements = [];

    /**
     * The imports needed to be injected in the stub.
     *
     * @var array<string|int, string>
     */
    protected array $imports = [];

    /**
     * The trait uses for class stubs.
     *
     * @var array<string|int, string>
     */
    protected array $uses = [];

    /**
     * The target class name.
     *
     * @var string|null
     */
    protected ?string $class = null;

    /**
     * The target class's namespace.
     *
     * @var string|null
     */
    protected ?string $namespace = null;

    /**
     * Create a new stub instance.
     *
     * @param  string  $stubName
     * @param  string|null  $class
     * @param  string|null  $namespace
     */
    public function __construct(string $stubName, string $class = null, string $namespace = null)
    {
        $this->stub = $stubName;
        $this->class = $class;
        $this->namespace = $namespace;
    }

    /**
     * Create a new stub instance.
     *
     * @param  string  $stubName
     * @return static
     */
    public static function make(string $stubName): static
    {
        return new static($stubName);
    }

    /**
     * Register replacements for the stub.
     *
     * @param  array  $replacements
     * @return $this
     */
    public function withReplacements(array $replacements): static
    {
        foreach (static::filterReplacements($replacements) as $key => $value) {
            $this->replacements[$key] = $value;
        }

        return $this;
    }

    /**
     * Filters out unacceptable replacements.
     *
     * @param  array  $replacements
     * @return array
     */
    public static function filterReplacements(array $replacements): array
    {
        $filtered = [];

        foreach ($replacements as $key => $value) {
            if (!is_string($key)) continue;

            $value = match (true) {
                is_string($value) => $value,
                is_null($value) => 'null',
                is_object($value) => get_class($value),
                is_array($value) => implode(static::REPLACEMENT_ARRAY_SEPARATOR, $value),
                $value instanceof Collection => $value->join(static::REPLACEMENT_ARRAY_SEPARATOR),
                default => null,
            };

            if (is_null($value)) continue;

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * @param  array  $imports
     * @return $this
     */
    public function withImports(array $imports): static
    {
        $filtered = filter_bool_array($imports);
        $filtered = array_filter($filtered, fn ($val) => is_string($val) && $val);
        $filtered = array_map(fn (string $val) => trim(str_replace('/', '\\', $val), '\\'), $filtered);

        $this->imports = array_unique(array_merge($this->imports, $filtered));

        return $this;
    }

    /**
     * Publish the stub at module path.
     *
     * @param  string  $path
     * @return void
     */
    public function publish(string $path): void
    {
        $path = $this->module ? $this->module->path . '/' . $path : $path;
        $stub_path = __DIR__ . "/../stubs/{$this->stub}.stub";

        $replacements = $this->getReplacements([
            'file_path' => $path,
            'absolute_path' => $abs_path = base_path($path),
            'uses' => $this->getUsages(),
            'imports' => $this->getImports(),
        ]);

        $content = File::get(base_path($stub_path));
        $content = $this->parseContent($content, $replacements);

        File::ensureDirectoryExists(dirname($abs_path));
        File::put($abs_path, $content);
    }

    /**
     * Get the current replacements and merge new optional ones.
     *
     * @param  array  $merge
     * @return array<string, mixed>
     */
    public function getReplacements(array $merge = []): array
    {
        $replacements = [];

        if ($this->module) {
            $replacements['module_name'] = $this->module->name;
            $replacements['module_key'] = $this->module->key;
            $replacements['module_namespace'] = $this->module->namespace;
            $replacements['module_path'] = $this->module->path;
        }

        $replacements = array_merge($replacements, $this->replacements);

        return count($merge) ? array_merge($replacements, $merge) : $replacements;
    }

    /**
     * Generate the `use` string.
     *
     * @return string|null
     */
    public function getUsages(): ?string
    {
        if (count($this->uses)) {
            return sprintf('use %s;', implode(', ', $this->uses));
        }

        return null;
    }

    /**
     * Generate the imports block.
     *
     * @return string|null
     */
    public function getImports(): ?string
    {
        if (count($this->imports)) {
            return implode("\n", array_map(fn (string $import) => str_replace('\\', '\\\\', "use $import;"), $this->imports));
        }

        return null;
    }

    /**
     * Replace placeholders with values for provided replacements in stub content.
     *
     * @param  string  $content
     * @param  array<string, mixed>  $replacements
     * @return string
     */
    public static function parseContent(string $content, array $replacements = []): string
    {
        foreach ($replacements as $key => $value) {
            $content = preg_replace("/{{\s?$key\s?}}/", $value, $content);
        }

        return $content;
    }

    /**
     * Set the module for current stub.
     *
     * @param  \Azzazkhan\ModularLaravel\Factories\StubModule  $module
     * @return $this
     */
    public function forModule(StubModule $module): static
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Registers trait usages for class stubs.
     *
     * @param  array  $traits
     * @return $this
     */
    public function uses(array $traits): static
    {
        $filtered = filter_bool_array($traits);
        $filtered = array_filter($filtered, fn ($val) => is_string($val) && $val);

        $this->uses = array_unique(array_merge($this->uses, $filtered));

        return $this;
    }
}
