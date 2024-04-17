<?php

namespace Azzazkhan\ModularLaravel\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

abstract class ServiceProvider extends BaseServiceProvider
{
    const string MODULE = '';

    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected array $listen = [];

    /**
     * The model observers for your application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected array $observers = [];

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [];

    /**
     * Boot the module.
     */
    protected function bootModule(): void
    {
        $this->registerEventListeners();
        $this->registerModelObservers();
        $this->registerModelPolicies();
        $this->registerScheduledTasks();
    }

    /**
     * Extract and register event listeners from `listen` property.
     */
    protected function registerEventListeners(): void
    {
        foreach ($this->listen as $event => $listeners) {
            if (!is_string($event)) {
                continue;
            }

            $listeners = array_filter(Arr::wrap($listeners), fn ($class) => is_string($class));

            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Extract and register model observers from `observers` property.
     */
    protected function registerModelObservers(): void
    {
        foreach ($this->observers as $model => $observers) {
            if (!is_string($model)) {
                continue;
            }

            $observers = array_filter(Arr::wrap($observers), fn ($class) => is_string($class));

            foreach ($observers as $observer) {
                call_user_func([$model, 'observe'], $observer);
            }
        }
    }

    /**
     * Extract and register model policies from `policies` property.
     */
    protected function registerModelPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            if (is_string($model) && is_string($policy)) {
                Gate::policy($model, $policy);
            }
        }
    }

    /**
     * Register scheduled tasks.
     */
    protected function registerScheduledTasks(): void
    {
        // $this->app->booted(function () {
        //     // Schedule::command('inspire')->daily();
        // });
    }
}
