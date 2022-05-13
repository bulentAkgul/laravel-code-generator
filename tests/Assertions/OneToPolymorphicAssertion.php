<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait OneToPolymorphicAssertion
{
    public $methods = ['oto' => 'morphOne', 'otm' => 'morphMany'];

    public function assertCase($from, $to, $models)
    {
        $this->assertModels($from, $to, $models);
        $this->assertMigrations($from, $to, $models);
    }

    private function assertModels($from, $to, $models)
    {
        foreach ($models as $role => $model) {
            if (!$model['path']) continue;

            $this->assertFileExists($model['path']);

            $pairs = $this->getPair($models, $model['name']);

            $add = count($$role['uses']);

            $expectation =  AppendUses::_($$role['uses'], $add) + [
                 7 + $add => "class {$model['name']} extends Model",
                11 + $add => $this->setFunctionDeclaration($pairs, $to, $role),
                13 + $add => $this->setCodeLine($to, $pairs)
            ];

            $content = file($model['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, array $to, string $role): string
    {
        return 'public function ' . ($role == 'to' ? "{$to['singular']}able" : ConvertCase::camel($pairs[0], $this->mode == 'oto')) . '()';
    }

    private function setCodeLine($to, $pairs)
    {
        $method = $pairs[0] == $to['model'] ? $this->methods[$this->mode] : 'morphTo';

        return implode('', [
            'return $this->',
            $method,
            '(',
            $method == 'morphTo' ? '' : implode(', ', [
                "{$pairs[0]}::class", "'{$to['singular']}able'"
            ]),
            ');'
        ]);
    }

    private function assertMigrations($from, $to, $models)
    {
        $migrations = $this->setMigrations(
            [$from['passed'], $to['passed'], ''],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 'package'))
        );

        foreach ($migrations as $role => $migration) {
            if (!$migration['path']) continue;

            $this->assertFileExists($migration['path']);

            $expectation = [
                10 => 'Schema::create(' . Text::wrap($migration['name'], 'sq') . ', function (Blueprint $table) {',
                13 => $role == 'to' ? '$table->integer' . Text::inject("{$to['singular']}able_id", ['(', 'sq']) . ';' : '});',
                14 => $role == 'to' ? '$table->string' . Text::inject("{$to['singular']}able_type", ['(', 'sq']) . ';' : '}',
            ];

            $content = file($migration['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }
}
