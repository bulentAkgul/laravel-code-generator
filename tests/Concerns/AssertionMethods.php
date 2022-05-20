<?php

namespace Bakgul\CodeGenerator\Tests\Concerns;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Text;

trait AssertionMethods
{
    public function setMigrations(array $names, array $packages = ['', '', ''])
    {
        $migrations = [];

        foreach ($names as $i => $name) {
            $migrations[$this->roles[$i]] = [
                'name' => $name,
                'path' => $this->migrationPath($name, $packages[$i]),
                'package' => $this->setPackage($packages[$i])
            ];
        }

        return $migrations;
    }

    private function migrationPath(string $name, string $package)
    {
        return $this->existingPath(Path::glue([
            $package ? Path::package($package) : $this->testPackage['path'],
            'database',
            'migrations',
            "000000_create_{$name}_table.php"
        ]));
    }

    public function getPair($models, $name)
    {
        return array_values(array_filter(
            array_map(fn ($x) => Arry::get($x, 0), array_values($models)),
            fn ($x) => $x != $name
        ));
    }

    public function makeLocalKey(string $key): string
    {
        return $key == 'id' ? '' : '$table->integer' . $this->inject($key) . ';';
    }
    
    public function close()
    {
        return '});';
    }

    public function inject($value) {
        return Text::inject($value, ['(', 'sq']);
    }
}