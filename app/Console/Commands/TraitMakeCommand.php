<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class TraitMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:trait {name}';

    protected $description = 'Create a trait';

    protected $type = 'Trait';

    protected function getStub()
    {
        return 'stubs/trait.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Traits';
    }
}
