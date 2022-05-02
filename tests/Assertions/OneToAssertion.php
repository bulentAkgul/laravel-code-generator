<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Assertions\AssertionHelpers\AppendUses;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait OneToAssertion
{
    public $methods = ['oto' => 'hasOne', 'otm' => 'hasMany'];

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

            $expectation = AppendUses::_($$role[4], $add) + [
                 7 + $add => "class {$model[0]} extends Model",
                11 + $add => $this->setFunction($pairs, $role),
                13 + $add => $this->codeLine($from, $to, $pairs)
            ];

            $content = file($model[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    private function setFunction(array $pairs, string $role): string
    {
        return 'public function ' . ConvertCase::camel($pairs[0], $this->mode == 'oto' ?: $role == 'to') . '()';
    }

    private function codeLine($from, $to, $pairs)
    {
        $method = $pairs[0] == $to[2] ? $this->methods[$this->mode] : 'belongsTo';

        return implode('', [
            'return $this->',
            $method,
            '(',
            "{$pairs[0]}::class",
            $method != 'belongsTo'
                ? Text::append(implode(', ', array_filter([
                    $to[3] ? "'{$to[3]}'" : ($from[3] ? "'{$from[0]}_id'" : ''),
                    "'{$from[3]}'"
                ], fn ($x) => $x && $x != "''")), ', ')
                : '',
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
                10 => 'Schema::create(' . Text::inject($migration[0], "'") . ', function (Blueprint $table) {',
                13 => $role == 'to' ? '$table->foreignId(' . Text::inject($to[3] ?: "{$from[0]}_id", "'") . ')->constrained(' . Text::inject($from[1], "'") . ');' : '});'
            ];

            $content = file($migration[1]);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }
}
