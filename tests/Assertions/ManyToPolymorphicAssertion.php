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
        $this->assertModels($from, $to, $mediator, $models);
        $this->assertMigrations($from, $to, $mediator, $models);
    }

    private function assertModels($from, $to, $mediator, $models)
    {
        foreach ($models as $role => $model) {
            if (!$model[0]) continue;

            $this->assertFileExists($model[1]);

            $pairs = $this->getPair($models, $model[0]);
            
            $add = count($$role[4]);
            $remove = $role == 'mediator' ? 1 : 0;

            $expectation = AppendUses::_($$role[4], $add) + [
                7 + $add - $remove => "class {$model[0]} extends " . ($role == 'mediator' ? 'Pivot' : 'Model')
            ] + ($role == 'from' ? [
                11 + $add - $remove => $this->setFunctionDeclaration($pairs, $role),
                13 + $add - $remove => $this->setCodeLine($mediator, $pairs, $role)
            ] : []);

            $content = file($model[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, string $role): string
    {
        if ($role == 'mediator') return '';

        return 'public function ' . ConvertCase::camel($pairs[0], false) . '()';
    }

    private function setCodeLine($mediator, $pairs, $role)
    {
        return implode('', [
            'return $this->',
            ['from' => 'morphToMany', 'to' => 'morphedByMany'][$role],
            Text::inject("{$pairs[0]}::class, '{$mediator[0]}'", ['(']),
            ';'
        ]);
    }

    private function addKeys($from, $to, $mediator, $pairs, $role)
    {
        $pair = Arry::find([$from, $to], $pairs[0])['value'];

        $keys = [$pair[3], $$role[3]];

        if (!array_filter($keys)) {
            return !$pairs[1] && $mediator[0] != $mediator[5]
                ? Text::append(Text::inject(ConvertCase::snake($mediator[5]), "'"), ', ')
                : '';
        }

        $keys[0] = $keys[0] ?: "{$pair[0]}_id";
        $keys[1] = $keys[1] ?: "{$$role[3]}_id";

        return Text::append(implode(', ', array_map(
            fn ($x) => Text::inject($x, "'"),
            [ConvertCase::snake($mediator[5] ?: $mediator[0]), ...$keys]
        )), ', ');
    }

    private function addUsing($mediator)
    {
        return $mediator[0] ? '->using(' . $mediator[0] . '::class)' : '';
    }

    private function assertMigrations($from, $to, $mediator, $models)
    {
        $migrations = $this->migrations(
            [$from[1], $to[1], $mediator[5] ?: $mediator[1]],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 2))
        );

        foreach ($migrations as $role => $migration) {
            $this->assertFileExists($migration[1]);

            $expectation = [
                10 => 'Schema::create(' . Text::inject($migration[0], "'") . ', function (Blueprint $table) {',
                13 => $this->migrationLine($role, $mediator, 'integer'),
                14 => $this->migrationLine($role, $mediator, 'string'),
            ];

            $content = file($migration[1]);

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
            Text::inject("'{$mediator[0]}{$suffix}'", ['(']),
            ';'
        ]);
    }
}