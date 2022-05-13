<?php

namespace Bakgul\CodeGenerator\Tests\Assertions;

use Bakgul\CodeGenerator\Tests\Concerns\AssertionMethods;
use Bakgul\CodeGenerator\Tests\Functions\AppendUses;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Arr;

trait HasThroughAssertion
{
    use AssertionMethods;

    public $methods = ['oto' => 'hasOneThrough', 'otm' => 'hasManyThrough'];

    public function assertCase($from, $to, $mediator, $models)
    {
        $this->assertModels($from, $to, $mediator, $models);
        $this->assertMigrations($from, $to, $mediator, $models);
    }

    private function mediatorKeys(string $key): array
    {
        return array_map(
            fn ($x) => $x ?: 'id',
            explode('.', str_contains($key, '.') ? $key : "{$key}.")
        );
    }

    private function assertModels($from, $to, $mediator, $models)
    {
        foreach ($models as $role => $model) {
            if (!$model['path']) continue;

            $this->assertFileExists($model['path']);

            $pairs = $this->getPair($models, $model['name']);

            $add = count($$role['uses']);

            $expectation = AppendUses::_($$role['uses'], $add) + [
                7 + $add => "class {$model['name']} extends Model"
            ] + ($role == 'from' ? [
                11 + $add => $this->setFunctionDeclaration($pairs, $role),
                13 + $add => $this->setCodeLine($from, $to, $mediator, $pairs)
            ] : []);

            $content = file($model['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]), "{$i} - {$model['path']}");
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
        $method = $pairs[0] == $to['model'] ? $this->methods[$this->mode] : 'belongsTo';

        return implode('', [
            'return $this->',
            $method,
            '(',
            "{$pairs[0]}::class, {$pairs[1]}::class",
            $this->addKeysToModelMethodDetail($from, $to, $mediator),
            ');'
        ]);
    }

    private function addKeysToModelMethodDetail($from, $to, $mediator)
    {
        $mkTail = $mediator['key'][0] != 'id' ? $mediator['key'][0] : ($from['key'] == 'id' ? '' : $from['key']);
        $mk = $mkTail ? "{$from['singular']}_{$mkTail}" : '';

        $tkTail = $to['key'] != 'id' ? $to['key'] : $mediator['key'][1];
        $tk = $tkTail == 'id' ? '' : "{$mediator['singular']}_{$tkTail}";

        $fi = $from['key'] == 'id' ? '' : $from['key'];
        $mi = $mediator['key'][1] == 'id' ? '' : $mediator['key'][1];

        $keys = [
            'mediator_key' => $mk ?: (self::hasTail([$tk, $fi, $mi]) ? "{$from['singular']}_id" : ''),
            'to_key' => $tk ?: (self::hasTail([$fi, $mi]) ? "{$mediator['singular']}_id" : ''),
            'from_id' => $fi ?: (self::hasTail([$mi]) ? 'id' : ''),
            'mediator_id' => $mi,
        ];

        return Text::append(implode(', ', array_map(
            fn ($x) => Text::wrap($x, 'sq'),
            array_filter(array_values($keys))
        )), ', ');
    }

    private static function hasTail($items)
    {
        return array_reduce($items, fn ($p, $c) => $p || $c, false);
    }

    private function assertMigrations($from, $to, $mediator, $models)
    {
        $migrations = $this->setMigrations(
            [$from['passed'], $to['passed'], $mediator['passed']],
            array_map(fn ($x) => $x ?? '', Arr::pluck($models, 'package'))
        );

        foreach ($migrations as $role => $migration) {
            if (!$migration['path']) continue;

            $this->assertFileExists($migration['path']);

            $expectation = [
                10 => $this->schemaLine($migration),
                13 => $this->migrationFirstLine($role, $from, $to, $mediator),
                14 => $this->migrationSecondLine($role, $from, $to, $mediator),
                15 => $this->migrationThirdLine($role, $from, $to, $mediator),
            ];

            $content = file($migration['path']);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]), "{$i} - {$migration['path']}");
            }
        }
    }

    private function schemaLine($migration)
    {
        return 'Schema::create(' . Text::wrap($migration['name'], 'sq') . ', function (Blueprint $table) {';
    }

    private function migrationFirstLine($role, $from, $to, $mediator)
    {
        return match ($role) {
            'from' => $this->setFromFirstLine($from),
            'to' => $this->setToFirstLine($to, $mediator),
            'mediator' => $this->setMediatorFirstLine($from, $mediator)
        };
    }

    private function migrationSecondLine($role, $from, $to, $mediator)
    {
        return match ($role) {
            'from' => $this->setFromSecondLine($from),
            'to' => $this->setToSecondLine($to, $mediator),
            'mediator' => $this->setMediatorSecondLine($from, $mediator)
        };
    }

    private function migrationThirdLine($role, $from, $to, $mediator)
    {
        return match ($role) {
            'from' => $this->setFromThirdLine($from),
            'to' => $this->setToThirdLine($to, $mediator),
            'mediator' => $this->setMediatorThirdLine($from, $mediator)
        };
    }

    private function setFromFirstLine($from)
    {
        return $this->isFromOld($from)
            ? $this->makeLocalKey($from['key'])
            : $this->close();
    }

    private function setFromSecondLine($from)
    {
        return $this->isFromOld($from) ? $this->close() : '}';
    }

    private function setFromThirdLine($from)
    {
        return $this->isFromOld($from) ? '}' : '';
    }

    private function setToFirstLine($to, $mediator)
    {
        $mediator = $this->setMediator($mediator, 'to');

        return $this->isToOld($to, $mediator)
            ? $this->setOldSyntaxDeclaration([$mediator, $to])
            : $this->setNewSyntax($mediator);
    }

    private function setToSecondLine($to, $mediator)
    {
        $mediator = $this->setMediator($mediator, 'to');
        
        return $this->isToOld($to, $mediator)
            ? $this->setOldSyntaxDetails([$mediator, $to])
            : $this->close();
    }

    private function setToThirdLine($to, $mediator)
    {
        return $this->isToOld($to, $this->setMediator($mediator, 'to')) ? $this->close() : '}';
    }

    private function setMediatorFirstLine($from, $mediator)
    {
        if ($this->hasMediatorLocalKey($mediator)) return $this->makeLocalKey($mediator['key'][1]);

        $mediator = $this->setMediator($mediator, 'from');

        return $this->isMediatorOld($mediator, $from)
            ? $this->setOldSyntaxDeclaration([$from, $mediator])
            : $this->setNewSyntax($from);
    }

    private function setMediatorSecondLine($from, $mediator)
    {
        $hasLocalKey = $this->hasMediatorLocalKey($mediator);
        $mediator = $this->setMediator($mediator, 'from');

        if ($hasLocalKey) {
            return $this->isMediatorOld($mediator, $from)
                ? $this->setOldSyntaxDeclaration([$from, $mediator])
                : $this->setNewSyntax($from);
        }

        return $this->isMediatorOld($mediator, $from)
            ? $this->setOldSyntaxDetails([$from, $mediator])
            : $this->close();
    }

    private function setMediatorThirdLine($from, $mediator)
    {
        $medToKey = $mediator['key'][1];
        $mediator = $this->setMediator($mediator, 'from');

        if ($medToKey != 'id') {
            return $mediator['key'] != 'id' || $from['key'] != 'id'
                ? $this->setOldSyntaxDetails([$from, $mediator])
                : $this->close();
        }

        return $mediator['key'] != 'id' || $from['key'] != 'id'
            ? $this->close()
            : '}';
    }

    private function setOldSyntaxDetails($sides)
    {
        return implode('', [
            '$table->foreign(',
            "'{$this->makeKey($sides)}'",
            ')->references(',
            "'{$sides[0]['key']}'",
            ')->on(',
            "'{$sides[0]['passed']}'",
            ');'
        ]);
    }

    private function setOldSyntaxDeclaration($sides)
    {
        return '$table->unsignedBigInteger(' . "'{$this->makeKey($sides)}'" . ');';
    }

    private function makeKey($sides)
    {
        return "{$this->snake($sides[0]['singular'])}_{$this->setKey($sides)}";
    }

    private function setKey($sides)
    {
        return $sides[1]['key'] == 'id'
            ? ($sides[0]['key'] == 'id' ? 'id' : $sides[0]['key'])
            : $sides[1]['key'];
    }

    private function setNewSyntax(array $side)
    {
        return '$table->foreignId'
            . Text::inject("{$side['singular']}_id", ['(', 'sq'])
            . '->constrained'
            . Text::inject($side['passed'], ['(', 'sq'])
            . ';';
    }

    private function setMediator($mediator, $pair)
    {
        $mediator['key'] = $mediator['key'][$pair == 'to' ? 1 : 0];

        return $mediator;
    }

    private function isFromOld($from)
    {
        return $from['key'] != 'id';
    }

    private function isToOld($to, $mediator)
    {
        return $to['key'] != 'id' || $mediator['key'] != 'id';
    }

    private function isMediatorOld($mediator, $from)
    {
        return $mediator['key'] != 'id' || $from['key'] != 'id';
    }

    private function hasMediatorLocalKey($mediator)
    {
        return $mediator['key'][1] != 'id';
    }
}
