<?php

namespace Azzazkhan\ModularLaravel\Factories;

readonly class StubModule
{
    public function __construct(public string $name, public string $key, public string $namespace, public string $path)
    {
        //
    }
}
