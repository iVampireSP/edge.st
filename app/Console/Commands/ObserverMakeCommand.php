<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ObserverMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:observer {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a observer';


    protected $type = 'Observer';


    protected function getStub()
    {
        return 'stubs/observer.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Observers';
    }
}
