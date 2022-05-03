<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait HasThroughAssertion
{
    public $methods = ['oto' => 'hasOneThrough', 'otm' => 'hasManyThrough'];

    public function assertCase($from, $to, $mediator, $models)
    {
        $this->assertModels($from, $to, $mediator, $models);
        $this->assertMigrations($from, $to, $mediator, $models);
    }

    private function assertModels($from, $to, $mediator, $models)
    {

        foreach ($models as $role => $model) {
            if (!$model) continue;

            $this->assertFileExists($model[1]);

            $pairs = $this->getPair($models, $model[0]);
            
            $add = count($$role[4]);

            $expectation = AppendUses::_($$role[4], $add) + [
                7 + $add => "class {$model[0]} extends Model"
            ] + ($role == 'from' ? [
                11 + $add => $this->setFunctionDeclaration($pairs, $role),
                13 + $add => $this->setCodeLine($from, $to, $mediator, $pairs)
            ] : []);

            $content = file($model[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunctionDeclaration(array $pairs, string $role): string
    {
        if ($role != 'from') return '';

        return 'public function ' . ConvertCase::camel(
            $this->mode == 'oto' ? "{$pairs[1]}-{$pairs[0]}" : $pairs[0],
            $this->mode == 'oto'
        ) . '()';
    }

    private function setCodeLine($from, $to, $mediator, $pairs)
    {
        $method = $pairs[0] == $to[2] ? $this->methods[$this->mode] : 'belongsTo';

        return implode('', [
            'return $this->',
            $method,
            '(',
            "{$pairs[0]}::class, {$pairs[1]}::class",
            $this->addKeys($from, $to, $mediator, $pairs),
            ');'
        ]);
    }

    private function addKeys($from, $to, $mediator, $pairs)
    {
        return Text::append(implode(', ', array_map(
            fn ($x) => Text::inject($x, "'"),
            array_filter([$mediator[3] ?: ($to[3] ? "{$from[0]}_id" : ''), $to[3]])
        )), ', ');
    }

    private function assertMigrations($from, $to, $mediator, $models)
    {
        $migrations = $this->migrations(
            [$from[1], $to[1], ''],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 2))
        );

        foreach ($migrations as $role => $migration) {
            if (!$migration) continue;

            $this->assertFileExists($migration[1]);

            $expectation = [
                10 => 'Schema::create(' . Text::inject($migration[0], "'") . ', function (Blueprint $table) {',
                13 => $this->migrationLine($role, $to, $mediator)
            ];

            $content = file($migration[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function migrationLine($role, $to, $mediator)
    {
        if ($role == 'from') return '});';

        $key = Text::inject(
            $role == 'to'
                ? ($to[3] ?: "{$mediator[0]}_id")
                : ($mediator[3] ?: "{$to[0]}_id"),
            "'"
        );
        
        $table = Text::inject($role == 'to' ? $mediator[1] : $to[1], "'");

        return '$table->foreignId(' . $key . ')->constrained(' . $table . ');';
    }
}
