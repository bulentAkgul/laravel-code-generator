<?php

namespace Bakgul\CodeGenerator;

use Bakgul\Kernel\Concerns\HasConfig;
use Illuminate\Support\ServiceProvider;

class CodeGeneratorServiceProvider extends ServiceProvider
{
    use HasConfig;

    public function boot()
    {
        $this->commands([
            \Bakgul\CodeGenerator\Commands\GenerateRelationshipCommand::class,
        ]);
    }

    public function register()
    {
        $this->registerConfigs(__DIR__ . DIRECTORY_SEPARATOR . '..');
    }
}
