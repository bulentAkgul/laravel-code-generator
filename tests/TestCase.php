<?php

namespace Bakgul\CodeGenerator\Tests;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Tests\Services\TestDataService;
use Bakgul\Kernel\Tests\Tasks\SetupTest;
use Bakgul\Kernel\Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $roles = ['from', 'to', 'mediator'];
    protected $mode;
    protected $scenario;

    protected function init(string $mode = '', string $scenario = '')
    {
        $this->mode = $mode ?: Arry::random(['oto', 'otm']);
        $this->scenario = $scenario ?: Arry::random(['sl', 'sp', 'pl']);
        $this->setupTest($this->scenario);
    }

    protected function setupTest(string $key)
    {
        $this->testPackage = (new SetupTest)(TestDataService::standalone($key));
    }

    protected function create(string $specs)
    {
        $this->artisan("create:relation {$this->mode} {$specs}");
    }

    protected function setPackage($package)
    {
        return $package ?: (!Settings::standalone() ? $this->testPackage['name'] : '');
    }

    protected function snake($value, $isSingular = true)
    {
        return ConvertCase::snake($value, $isSingular);
    }

    // protected function database($folder)
    // {
    //     return Path::glue([$this->testPackage['path'], 'database', $folder]);
    // }

    // protected function defaultPivot(array $models): string
    // {
    //     return implode('_', Arry::sort(array_map(fn ($x) => $this->snake($x), array_keys($models))));
    // }
}
