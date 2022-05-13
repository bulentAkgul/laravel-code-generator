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

class TestCase extends BaseTestCase
{
    const ROLES = ['from', 'to', 'mediator'];
    protected $mode;
    protected $scenario;

    protected function init(string $mode = '', string $scenario = '')
    {
        $this->mode = $mode ?: Arry::random(['oto', 'otm'])[0];
        $this->scenario = $scenario ?: Arry::random(['sl', 'sp', 'pl'])[0];
        $this->setupTest($this->scenario);
    }

    protected function create(string $specs)
    {
        $this->artisan("create:relation {$this->mode} {$specs}");
    }

    protected function setupTest(string $key)
    {
        $this->testPackage = (new SetupTest)(TestDataService::standalone($key));
    }

    protected function names($base, $model = '')
    {
        return [
            'passed' => $this->snake($base, null),
            'singular' => $this->snake($base, true),
            'plural' => $this->snake($base, false),
            'model' => Convention::class($model ?: $base)
        ];
    }

    protected function setModels(array $names, array $packages = ['', '', ''])
    {
        Settings::set('file.model.pairs', ['']);

        $models = [];

        foreach ($names as $i => $name) {
            if ($i != 2) $this->createModel($name, $packages[$i]);

            $models[self::ROLES[$i]] = [
                'name' => $n = Convention::class($name),
                'package' => $this->setPackage($packages[$i]),
                'path' => $this->modelPath($n, $packages[$i]),
            ];
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

    private function setPackage($package)
    {
        return $package ?: (!Settings::standalone() ? $this->testPackage['name'] : '');
    }

    protected function setMigrations(array $names, array $packages = ['', '', ''])
    {
        $migrations = [];

        foreach ($names as $i => $name) {
            $migrations[self::ROLES[$i]] = [
                'name' => $name,
                'path' => $this->migrationPath($name, $packages[$i]),
                'package' => $this->setPackage($packages[$i])
            ];
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
        return $this->existingPath(Path::glue([
            $package ? Path::package($package) : $this->testPackage['path'],
            Settings::standalone('laravel') ? 'app' : 'src',
            'Models',
            "{$name}.php"
        ]));
    }

    private function migrationPath(string $name, string $package)
    {
        return $this->existingPath(Path::glue([
            $package ? Path::package($package) : $this->testPackage['path'],
            'database',
            'migrations',
            self::migration($name)
        ]));
    }

    protected function migration(string $name)
    {
        return Carbon::today()->format('Y_m_d') . "_000000_create_{$name}_table.php";
    }

    private function existingPath($path)
    {
        return file_exists($path) ? $path : '';
    }

    protected function database($folder)
    {
        return Path::glue([$this->testPackage['path'], 'database', $folder]);
    }

    protected function defaultPivot(array $models): string
    {
        return implode('_', Arry::sort(array_map(fn ($x) => $this->snake($x), array_keys($models))));
    }

    protected function snake($value, $isSingular = true)
    {
        return ConvertCase::snake($value, $isSingular);
    }
}
