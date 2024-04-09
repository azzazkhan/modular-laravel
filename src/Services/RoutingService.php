<?php

namespace Azzazkhan\ModularLaravel\Services;

use Closure;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

readonly class RoutingService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public ?string $health = '/up')
    {
        //
    }

    /**
     * Handle the service.
     */
    public function __invoke(): void
    {
        $this->registerWebRoutes($this->registerHealthRoute(...));
        $this->registerWebRoutes(base_path('routes/web.php'));

        // Only load API routes if enabled
        if (file_exists($path = base_path('routes/api.php'))) {
            $this->registerApiRoutes($path);
        }
    }

    /**
     * Wraps the provided routes definition closure with global web route
     * configuration.
     *
     * @param  array|string|\Closure  $routes
     * @param  string  $name
     * @return void
     */
    public function registerWebRoutes(array|string|Closure $routes, string $name = ''): void
    {
        $domain = config('app.domain');
        $router = $domain ? Route::domain($domain)->middleware('web') : Route::middleware('web');

        $router->name($name)->group($routes);
    }

    /**
     * Wraps the provided routes definition closure with global API route
     * configuration.
     *
     * @param  array|string|\Closure  $routes
     * @param  string  $name
     * @return void
     */
    public function registerApiRoutes(array|string|Closure $routes, string $name = ''): void
    {
        $subdomain = config('app.api-subdomain');
        $router = $subdomain ? Route::domain($subdomain) : Route::prefix('api');

        $router->middleware('api')->name($name)->group($routes);
    }

    /**
     * @return void
     */
    protected function registerHealthRoute(): void
    {
        if (!$this->health) return;

        Route::get($this->health, function () {
            Event::dispatch(new DiagnosingHealth);

            return View::file(base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/health-up.blade.php'));
        })->name('health');
    }
}
