<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Concerns\AssertionMethods;
use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait OneToAssertion
{
    use AssertionMethods;

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

            $expectation = AppendUses::_($$role['uses'], $add) + [
                7 + $add => "class {$model['name']} extends Model",
                11 + $add => $this->setFunctionDeclaration($pairs, $role),
                13 + $add => $this->setCodeLine($from, $to, $pairs)
            ];

            $content = file($model['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, string $role): string
    {
        return 'public function ' . ConvertCase::camel($pairs[0], $this->mode == 'oto' ?: $role == 'to') . '()';
    }

    private function setCodeLine($from, $to, $pairs)
    {
        return implode('', [
            'return $this->',
            $this->method($to, $pairs),
            '(',
            "{$pairs[0]}::class",
            $this->setKeys($from, $to),
            ');'
        ]);
    }

    private function method($to, $pairs)
    {
        return $pairs[0] == $to['model']
            ? ['oto' => 'hasOne', 'otm' => 'hasMany'][$this->mode]
            : 'belongsTo';
    }

    private function setKeys($from, $to)
    {
        return Text::append(implode(', ', array_map(
            fn ($x) => Text::wrap($x, 'sq'),
            array_filter(array_reverse([
                $pk = $this->pairKey($from),
                $this->foreignKey($from, $to, $pk),
            ]))
        )), ', ');
    }

    private function pairKey($from)
    {
        return $from['key'] == 'id' ? '' : $from['key'];
    }

    private function foreignKey($from, $to, $after)
    {
        return match (true) {
            $to['key'] != 'id' => "{$from['singular']}_{$to['key']}",
            $from['key'] != 'id' => "{$from['singular']}_{$from['key']}",
            default => $after ? "{$from['singular']}_id" : '',
        };
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
                10 => $this->setMigrationMethod($migration['name']),
                13 => $this->setMigrationFirstLine($role, $from, $to),
                14 => $this->setMigrationSecondLine($role, $from, $to)
            ];

            $content = file($migration['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setMigrationMethod(string $name): string
    {
        return 'Schema::create(' . Text::wrap($name, 'sq') . ', function (Blueprint $table) {';
    }

    private function setMigrationFirstLine($role, $from, $to): string
    {
        return match ($role) {
            'from' => $this->setFromFirstLine($from, $to),
            'to' => $this->setToFirstLine($from, $to),
        };
    }

    private function setMigrationSecondLine($role, $from, $to): string
    {
        return match ($role) {
            'from' => $this->setFromSecondLine($from, $to),
            'to' => $this->setToSecondLine($from, $to),
        };
    }

    private function setFromFirstLine($from, $to)
    {
        return $this->hasLocalKey($from['key'])
            ? $this->makeLocalKey($from['key'])
            : $this->close();
    }

    private function setFromSecondLine($from, $to)
    {
        return $this->hasLocalKey($from['key']) ? $this->close() : '}';
    }

    private function setToFirstLine($from, $to)
    {
        return $this->isNewSyntax($from, $to)
            ? $this->newSyntax($from, $to)
            : $this->oldSyntaxDeclaration($from, $to);
    }

    private function setToSecondLine($from, $to)
    {
        return $this->isNewSyntax($from, $to)
            ? $this->close()
            : $this->oldSyntaxDetails($from, $to);
    }

    private function newSyntax($from, $to)
    {
        return '$table->foreignId'
            . Text::inject("{$from['singular']}_" . ($to['key'] ?: $from['key'] ?: 'id'), ['(', 'sq'])
            . '->constrained'
            . Text::inject($from['passed'], ['(', 'sq']) . ';';
    }

    private function oldSyntaxDeclaration($from, $to)
    {
        return '$table->unsignedBigInteger'
            . Text::inject("{$from['singular']}_" . ($to['key'] != 'id' ? $to['key'] : $from['key']), ['(', 'sq'])
            . ';';
    }

    private function oldSyntaxDetails($from, $to)
    {
        return implode('', [
            '$table->foreign',
            $this->inject("{$from['singular']}_" . ($to['key'] != 'id' ? $to['key'] : $from['key'])),
            '->references',
            $this->inject($from['key']),
            '->on',
            $this->inject($from['passed']),
            ';'
        ]);
    }

    private function hasLocalKey($key)
    {
        return $key != 'id';
    }

    private function isNewSyntax($from, $to)
    {
        return $from['key'] == 'id' && $to['key'] == 'id';
    }
}
