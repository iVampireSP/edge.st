<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class DriverMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:driver {name}';

    protected $description = 'Create a driver';

    protected $type = 'Driver';

    protected function getStub()
    {
        return 'stubs/driver.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Drivers';
    }
}
