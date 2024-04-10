# Modular Laravel
This package is intended for adding modularization capability in your [Laravel 11](https://laravel.com/docs/11.x) applications. This package
is inspired by the [Modular Laravel](https://laracasts.com/series/modular-laravel) series on Laracasts and provides helpful artisan commands
for scaffolding modules and its components.

To install this package run the following command in your Laravel application.

```shell
composer require azzazkhan/modular-laravel
```

While installing this package composer will ask to allow `wikimedia/composer-merge-plugin` plugin. It should be allowed as it is needed for
properly registering the components for each module. In addition to allowing the plugin, you also need to define the merge plugin's
configuration in `extra` property of your `composer.json` file.

```json
{
    "extra": {
        "laravel": {
            "dont-discover": []
        },
        "merge-plugin": {
            "include": [
                "modules/*/composer.json"
            ]
        }
    }
}
```

Remember that `modules` is the folder name which will host all modules of your application and this needs to be the kebab-case version of
the configuration defined in `modules.namespace` config value.

Next, you need to swap out the default routing definition in `bootstrap/app.php` with the following one. The specified `RouteService` will
register your app's default `web` and `api` and health routes. The routing service also exposes some methods for properly registering web
and api routes for each of your application's modules.

```php
use Azzazkhan\ModularLaravel\Services\RoutingService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\App;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: fn () => App::call(RoutingService::class),
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Finally, update your `phpunit.xml` file by adding the following test suites and sources.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Modules">
            <directory>modules/*/tests/Feature</directory>
            <directory>modules/*/tests/Unit</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>modules</directory>
        </include>
        <exclude>
            <directory>modules/*/database</directory>
            <directory>modules/*/resources</directory>
            <directory>modules/*/tests</directory>
        </exclude>
    </source>
</phpunit>
```

Note that all `module:*` commands provided this package works mostly like their `make:*` counterpart generator commands with following
exceptions.

1. All commands supporting `--test` option will only generate an accompanying Pest test. For generating tests using PHPUnit, you need to use
   the `module:test` command and provide the test name and path.
