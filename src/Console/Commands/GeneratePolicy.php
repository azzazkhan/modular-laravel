<?php

namespace Azzazkhan\ModularLaravel\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;

class GeneratePolicy extends Generator
{
    protected string $type = 'policy';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:policy
                            {name : Name of the policy}
                            {--module= : Name of the module}
                            {--f|force : Create the class even if the policy already exists}
                            {--m|model= : The model that the policy applies to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new policy class';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        [$class, $path, $prefix] = $this->extractClassDetails($this->argument('name'), 'app/Policies');
        $class = $this->normalizePolicyName($class);
        [$path, $namespace] = ["$path/{$class}Policy.php", $this->namespace(['Policies', $prefix])];

        if (!$this->validateModuleExistence() || !$this->validateFileAbsence($path)) {
            return;
        }

        $stub = 'policy.plain';
        $replacements = [
            'class' => $class . 'Policy',
            'namespace' => $namespace,
            'user' => $user_class = 'User',
            'user_namespace' => User::class,
        ];

        if ($model = $this->option('model')) {
            $stub = 'policy';
            [$model_class, , $model_prefix] = $this->extractClassDetails(str_remove_prefix($model, 'models/'));
            $model_namespace = $this->namespace(['Models', $model_prefix, $model_class]);
            if ($model_class == $user_class) {
                $model_namespace = $this->namespace(['Models', $model_prefix, $model_class]) . " as {$model_class}Model";
                $model_class = $model_class . 'Model';
            }

            $replacements['model'] = $model_class;
            $replacements['model_namespace'] = $model_namespace;
            $replacements['model_variable'] = Str::camel($model_class);
        }

        $this->makeStub($stub)->withReplacements($replacements)->publish($path);

        $this->components->info("Policy [$path] created successfully.");
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizePolicyName(string $name): string
    {
        return str_remove_suffix($name, 'policy');
    }
}
