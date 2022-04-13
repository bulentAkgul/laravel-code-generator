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

        foreach (['User', 'Post'] as $name) {
            $models[$name] = Path::glue([$this->testPackage['path'], 'src', 'Models', "{$name}.php"]);
        }

        return $models;
    }

    protected function callCommand(string $type, array $models, string $pivot = '', array $modifiers = [])
    {
        $glue = Settings::seperators('modifier');

        $this->artisan(
            "code:rel {$this->testPackage['name']} {$type} " . implode(' ', array_filter([
                $models[0] . Text::append(Arry::get($modifiers, 0) ?? '', $glue),
                $models[1] . Text::append(Arry::get($modifiers, 1) ?? '', $glue),
                $pivot . Text::append(Arry::get($modifiers, 2) ?? '', $glue)
            ])) . Text::append(Arry::has(2, $models) ? "-T={$models[2]}" : '', ' ')
        );
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