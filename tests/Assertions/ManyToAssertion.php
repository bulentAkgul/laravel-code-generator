<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Concerns\AssertionMethods;
use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait ManyToAssertion
{
    use AssertionMethods;
    
    public function assertCase($from, $to, $mediator, $models)
    {
        $this->assertModels($from, $to, $mediator, $models);
        $this->assertMigrations($from, $to, $mediator, $models);
    }

    private function assertModels($from, $to, $mediator, $models)
    {
        foreach ($models as $role => $model) {
            if (!$model['path']) continue;

            $this->assertFileExists($model['path']);

            $pairs = $this->getPair($models, $model['name']);

            $add = count($$role['uses']);
            $remove = $role == 'mediator' ? 1 : 0;

            $expectation = AppendUses::_($$role['uses'], $add) + [
                8 + $add - $remove => "class {$model['name']} extends " . ($role == 'mediator' ? 'Pivot' : 'Model')
            ] + ($role == 'from' ? [
                17 + $add - $remove => $this->setFunctionDeclaration($pairs, $role),
                19 + $add - $remove => $this->setCodeLine($from, $to, $mediator, $models, $pairs, $role)
            ] : []);

            $content = file($model['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]), "{$i} {$model['path']}");
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, string $role): string
    {
        if ($role == 'mediator') return '';

        return 'public function ' . ConvertCase::camel($pairs[0], false) . '()';
    }

    private function setCodeLine($from, $to, $mediator, $models, $pairs, $role)
    {
        $using = $this->addUsing($models['mediator']);

        return implode('', [
            'return $this->belongsToMany(',
            "{$pairs[0]}::class",
            $this->addKeys($from, $to, $mediator, $pairs, $role, $using),
            "){$using};",
        ]);
    }

    private function addKeys($from, $to, $mediator, $pairs, $role, $using)
    {
        $pair = Arry::find([$from, $to], $pairs[0], 'model')['value'];

        $keys = array_map(
            fn ($x) => Text::wrap($x, 'sq'),
            $this->makeKeys([$pair['key'], $$role['key']], $role == 'from' ? $from : $to, $pair, $mediator)
        );

        return Text::append(implode(', ', $keys), ', ');
    }

    private function areKeysId($keys)
    {
        return !array_filter($keys, fn ($x) => $x != 'id');
    }

    private function isNotDefaultTable($from, $to, $default)
    {
        return $this->defaultPivotName($from, $to) != $default;
    }

    private function defaultPivotName($from, $to)
    {
        return  implode('_', array_map(
            fn ($x) => ConvertCase::snake($x, true),
            Arry::sort(Arr::pluck([$from, $to], 'singular'))
        ));
    }


    private function makeKeys($keys, $side, $pair, $mediator)
    {
        $output[2] = substr($keys[0], -3) == '_id' ? $keys[0] : ($keys[0] != 'id' ? "{$pair['singular']}_{$keys[0]}" : '');
        $output[1] = substr($keys[1], -3) == '_id' ? $keys[1] : ($keys[1] != 'id' || $output[2] ?  "{$side['singular']}_{$keys[1]}" : '');
        $output[0] = $this->isNotDefaultTable($side, $pair, $mediator['passed']) || $output[1] ? $mediator['passed'] : '';

        return array_filter(array_reverse($output));
    }

    private function setKeys($table, $isDefault, $keys)
    {
        return array_filter([
            $isDefault && !array_filter($keys) ? '' : $table,
            ...$keys
        ]);
    }

    private function addUsing($mediator)
    {
        return $mediator['name'] ? '->using(' . $mediator['name'] . '::class)' : '';
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
                13 => $this->migrationLine($role, $from, $to, true),
                14 => $this->migrationLine($role, $from, $to, false),
            ];

            $content = file($migration['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]), "{$role} {$i} {$migration['path']}");
            }
        }
    }

    private function migrationLine($role, $from, $to, $isFirstLine)
    {
        if ($role == 'mediator') {
            $side = $isFirstLine ? $from : $to;
            return $this->makeLocalKey(
                str_contains($side['key'], '_id')
                    ? $side['key']
                    : "{$side['singular']}_{$side['key']}"
            );
        }

        return $isFirstLine
            ? ($$role['key'] == 'id' ? $this->close() : $this->makeLocalKey($$role['key']))
            : ($$role['key'] == 'id' ? '}' : $this->close());
    }
}
