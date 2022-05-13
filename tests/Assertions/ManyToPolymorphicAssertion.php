<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait ManyToPolymorphicAssertion
{
    public function assertCase($from, $to, $mediator, $models)
    {
        $mediator = $this->mediator($to['singular']);

        $this->assertModels($from, $to, $mediator, $models);
        $this->assertMigrations($from, $to, $mediator, $models);
    }

    private function mediator($base)
    {
        return [
            'passed' => "{$base}ables",
            'singular' => "{$base}able",
        ];
    }

    private function assertModels($from, $to, $mediator, $models)
    {
        foreach ($models as $role => $model) {
            if (!$model['path']) continue;

            $this->assertFileExists($model['path']);

            $pairs = $this->getPair($models, $model['name']);

            $side = $role == 'from' ? $from : $to;
            
            $add = count($side['uses']);
            $remove = $role == 'mediator' ? 1 : 0;

            $expectation = AppendUses::_($side['uses'], $add) + [
                7 + $add - $remove => "class {$model['name']} extends " . ($role == 'mediator' ? 'Pivot' : 'Model')
            ] + ($role == 'from' ? [
                11 + $add - $remove => $this->setFunctionDeclaration($pairs, $role),
                13 + $add - $remove => $this->setCodeLine($mediator, $pairs, $role)
            ] : []);

            $content = file($model['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, string $role): string
    {
        return $role != 'mediator'
            ? 'public function ' . ConvertCase::camel($pairs[0], false) . '()'
            : '';
    }

    private function setCodeLine($mediator, $pairs, $role)
    {
        return implode('', [
            'return $this->',
            ['from' => 'morphToMany', 'to' => 'morphedByMany'][$role],
            Text::wrap("{$pairs[0]}::class, '{$mediator['singular']}'", '('),
            ';'
        ]);
    }

    private function addUsing($model)
    {
        return $model['name'] ? '->using(' . $model['name'] . '::class)' : '';
    }

    private function assertMigrations($from, $to, $mediator, $models)
    {
        $migrations = $this->setMigrations(
            [$from['passed'], $to['passed'], $mediator['passed']],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 'package'))
        );

        foreach ($migrations as $role => $migration) {
            $this->assertFileExists($migration['path']);

            $expectation = [
                10 => 'Schema::create(' . Text::wrap($migration['name'], 'sq') . ', function (Blueprint $table) {',
                13 => $this->migrationLine($role, $mediator, 'integer'),
                14 => $this->migrationLine($role, $mediator, 'string'),
            ];

            $content = file($migration['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function migrationLine($role, $mediator, $type)
    {
        if ($role != 'mediator') return $type == 'integer' ? '});' : '}';
        
        $suffix = $type == 'integer' ? '_id' : '_type';

        return implode('', [
            '$table->',
            $type,
            Text::wrap("'{$mediator['singular']}{$suffix}'", '('),
            ';'
        ]);
    }
}
