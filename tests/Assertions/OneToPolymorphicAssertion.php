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
            if (!$model) continue;

            $this->assertFileExists($model[1]);

            $pairs = $this->getPair($models, $model[0]);

            $add = count($$role[4]);

            $expectation =  AppendUses::_($$role[4], $add) + [
                 7 + $add => "class {$model[0]} extends Model",
                11 + $add => $this->setFunctionDeclaration($pairs, $to, $role),
                13 + $add => $this->setCodeLine($to, $pairs)
            ];

            $content = file($model[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, array $to, string $role): string
    {
        return 'public function ' . ($role == 'to' ? "{$to[0]}able" : ConvertCase::camel($pairs[0], $this->mode == 'oto')) . '()';
    }

    private function setCodeLine($to, $pairs)
    {
        $method = $pairs[0] == $to[2] ? $this->methods[$this->mode] : 'morphTo';

        return implode('', [
            'return $this->',
            $method,
            '(',
            $method == 'morphTo' ? '' : implode(', ', [
                "{$pairs[0]}::class", "'{$to[0]}able'"
            ]),
            ');'
        ]);
    }

    private function assertMigrations($from, $to, $models)
    {
        $migrations = $this->migrations(
            [$from[1], $to[1], ''],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 2))
        );

        foreach ($migrations as $role => $migration) {
            if (!$migration) continue;

            $this->assertFileExists($migration[1]);

            $expectation = [
                10 => 'Schema::create(' . Text::wrap($migration[0], 'sq') . ', function (Blueprint $table) {',
                13 => $role == 'to' ? '$table->integer' . Text::inject("{$to[0]}able_id", ['(', 'sq']) . ';' : '});',
                14 => $role == 'to' ? '$table->string' . Text::inject("{$to[0]}able_type", ['(', 'sq']) . ';' : '}',
            ];

            $content = file($migration[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }
}
