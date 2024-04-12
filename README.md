# Modular Laravel

This package is intended for adding modularization capability in your [Laravel 11](https://laravel.com/docs/11.x)
applications. This package is inspired by the [Modular Laravel](https://laracasts.com/series/modular-laravel) series on
Laracasts and provides helpful artisan commands for scaffolding modules and its components.

### Please note this package is created for personal use only and not intended to be used by public.

If you like this
package and want to introduce custom modifications, please feel free to fork it and add relevant changes.

To install this package run the following command in your Laravel application.

```shell
composer require azzazkhan/modular-laravel
```

While installing this package composer will ask to allow `wikimedia/composer-merge-plugin` plugin. It should be allowed
as it is needed for properly registering the components for each module. In addition to allowing the plugin, you also
need to define the merge plugin's configuration in `extra` property of your `composer.json` file.

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

Next, you need to swap out the default routing definition in `bootstrap/app.php` with the following one. The
specified `RouteService` will register your app's default `web`, `api` and health routes. It also exposes some methods
for properly registering web and api routes for each of your application's modules. The event configuration is used to
auto-discover Laravel 11's typed event listeners within all of your application modules.

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

Finally, update your `phpunit.xml` file and add the `testsuite`, `source/include` and `source/exclude` entries so
PHPUnit scan your module directories while discovering tests.

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

Note that all `module:*` commands provided this package serves same purpose as their `make:*` counterparts, thought some
modifications are made for easier scaffolding.

1. All commands supporting `--test` option will only generate an accompanying Pest test.
2. The controller, seeder, factory and observer commands will always append relevant
   Controller/Seeder/Factory/Observer/Request suffix to the class name if not provided while executing the command.
3. The controller generation command does not support resource related actions.
4. Event listeners are needed to be manually registered in module's service provider as Laravel doesn't support typed
   event listeners discovery outside `app` directory.
