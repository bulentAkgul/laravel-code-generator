<?php

namespace Bakgul\CodeGenerator\Tests;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Tests\TestCase as BaseTestCase;
use Carbon\Carbon;

class TestCase extends BaseTestCase
{
    protected function setModels()
    {
        config()->set('packagify.file.model.pairs', ['']);

        $models = [];

        foreach (['Post', 'Comment'] as $name) {
            $this->artisan("create:file {$name} model {$this->testPackage['name']}");
            
            $models[$name] = Path::glue([
                $this->testPackage['path'],
                Settings::standalone('laravel') ? 'app' : 'src',
                'Models',
                "{$name}.php"
            ]);
        }

        return $models;
    }

    protected function callCommand(string $type, array $models, string $pivot = '', array $modifiers = [], array $options = [])
    {
        $this->artisan("create:relation {$type} {$this->setArguments($models, $pivot, $modifiers)} {$this->setOptions($options)}");
    }

    private function setOptions(array $options)
    {
        $ops = [];

        foreach (array_filter($options) as $key => $value) {
            $ops[] = $value === true ? "--{$key}" : "--{$key}={$value}";
        }

        return implode(' ', $ops);
    }

    private function setArguments($models, $pivot, $modifiers)
    {
        $glue = Settings::seperators('modifier');

        return implode(' ', array_filter([
            $this->setFrom($models, $modifiers, $glue),
            $this->setTo($models, $modifiers, $glue),
            $this->setPivot($pivot, $modifiers, $glue)
        ]));
    }

    private function setFrom($models, $modifiers, $glue)
    {
        return "{$this->testPackage['name']}/{$models[0]}"
            . Text::append(Arry::get($modifiers, 0) ?? '', $glue);
    }

    private function setTo($models, $modifiers, $glue)
    {
        return $models[1] . Text::append(Arry::get($modifiers, 1) ?? '', $glue);
    }

    private function setPivot($pivot, $modifiers, $glue)
    {
        return $pivot . Text::append(Arry::get($modifiers, 2) ?? '', $glue);
    }

    protected function getPair($models, $name)
    {
        return array_values(array_filter(array_keys($models), fn ($x) => $x != $name))[0];
    }

    protected function migration(string $pivot)
    {
        return Carbon::today()->format('Y_m_d') . "_000000_create_{$pivot}_table.php";
    }

    protected function database($folder)
    {
        return Path::glue([$this->testPackage['path'], 'database', $folder]);
    }

    protected function defaultPivot(array $models): string
    {
        return implode('_', Arry::sort(array_map(fn ($x) => ConvertCase::snake($x, true), array_keys($models))));
    }
}
