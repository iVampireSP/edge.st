<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class SupportMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:support {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a support';

    protected $type = 'Support';


    protected function getStub()
    {
        return 'stubs/supports.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Supports';
    }
}
