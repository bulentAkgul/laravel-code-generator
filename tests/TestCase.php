<?php

namespace Bakgul\CodeGenerator\Tests;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Tests\Services\TestDataService;
use Bakgul\Kernel\Tests\Tasks\SetupTest;
use Bakgul\Kernel\Tests\TestCase as BaseTestCase;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestCase extends BaseTestCase
{
    const ROLES = ['from', 'to', 'mediator'];
    protected $mode;

    protected function create(string $specs)
    {
        $this->artisan("create:relation {$this->mode} {$specs}");
    }

    protected function setupTest(string $key)
    {
        $this->testPackage = (new SetupTest)(TestDataService::standalone($key));
    }

    protected function names($base)
    {
        return [$base, Str::plural($base), Convention::class($base)];
    }

    protected function setModels(array $names, array $packages = ['', '', ''])
    {
        config()->set('packagify.file.model.pairs', ['']);

        $models = [];

        foreach ($names as $i => $name) {
            if ($name) {
                if (!($this->mode == 'mtm' && $i == 2)) $this->createModel($name, $packages[$i]);
                $models[self::ROLES[$i]] = [$name, $this->modelPath($name, $packages[$i]), $packages[$i]];
            } else {
                $models[self::ROLES[$i]] = [];
            }
        }

        return $models;
    }

    private function createModel(string $name, string $package)
    {
        $this->artisan(implode(' ', array_filter([
            "create:file {$name} model",
            Settings::standalone() ? '' : ($package ?: $this->testPackage['name'])
        ])));
    }

    protected function migrations(array $names, array $packages = ['', '', ''])
    {
        $migrations = [];

        foreach ($names as $i => $name) {
            $name = $name ? ConvertCase::snake($name) : '';
            $migrations[self::ROLES[$i]] = $name ? [$name, $this->migrationPath($name, $packages[$i])] : [];
        }

        return $migrations;
    }

    protected function getPair($models, $name)
    {
        return array_values(array_filter(
            array_map(fn ($x) => Arry::get($x, 0), array_values($models)),
            fn ($x) => $x != $name
        ));
    }

    private function modelPath(string $name, string $package)
    {
        return Path::glue([
            $package ? Path::package($package) : $this->testPackage['path'],
            Settings::standalone('laravel') ? 'app' : 'src',
            'Models',
            "{$name}.php"
        ]);
    }

    protected function migration(string $name)
    {
        return Carbon::today()->format('Y_m_d') . "_000000_create_{$name}_table.php";
    }

    private function migrationPath(string $name, string $package)
    {
        return Path::glue([
            $package ? Path::package($package) : $this->testPackage['path'],
            'database',
            'migrations',
            self::migration($name)
        ]);
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
