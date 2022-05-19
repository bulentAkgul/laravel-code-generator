<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\IsKeyFull;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class SetMigrationLine
{
    public static function _(array $request, string $side): string
    {
        return match ($request['attr']['relation']) {
            'mtm' => self::setLinesForMTM($request, $side),
            default => self::setLinesForOTX($request, $side),
        };
    }

    private static function setLinesForMTM($request, $side)
    {
        return match (true) {
            $request['attr']['polymorphic'] => self::setLinesForPolymorphic($request, $side),
            default => self::setLinesForDefaultMTM($request, $side),
        };
    }

    private static function setLinesForDefaultMTM($request, $side)
    {
        if ($side != 'mediator') {
            return $request['attr']["{$side}_key"] != 'id' ? self::makeLocalKey($request['attr']["{$side}_key"]) : '';
        }

        $lines = [];
        
        foreach (['from', 'to'] as $side) {
            $lines[] = self::makeLocalKey(
                IsKeyFull::_($request['attr'][$side . '_key'])
                    ? $request['attr'][$side . '_key']
                    : "{$request['map'][$side]}_{$request['attr'][$side . '_key']}"
            );
        }

        return implode(self::glue(';'), $lines);
    }

    private static function setLinesForOTX($request, $side)
    {
        return match (true) {
            $request['attr']['polymorphic'] => self::setLinesForPolymorphic($request, $side),
            $request['attr']['is_through'] => self::setLinesForThroughOTX($request, $side),
            default => self::setLinesForDefaultOTX($request, $side),
        };
    }

    private static function setLinesForPolymorphic($request, $side)
    {
        return  $side != 'mediator' ? '' : implode(self::glue(';'), [
            self::makeLocalKey("{$request['map']['able']}_id"),
            '$table->string' . self::inject("{$request['map']['able']}_type"),
        ]);
    }

    private static function setLinesForThroughOTX($request, $side)
    {
        if (self::hasLocalLineOnly($side)) return self::setLocalLine($request['attr'], $side, $request['attr']['from_key']);

        $foreignLine = self::setForeignLineForThroughOTX($request, $side);

        if (self::hasForeignLineOnly($side)) return $foreignLine;

        $localLine = self::setLocalLine($request['attr'], $side, $request['attr']['mediator_t_key']);

        return implode(self::glue(';'), array_filter([$localLine, $foreignLine]));
    }

    private static function setForeignLineForThroughOTX($request, $side)
    {
        $pair = $side == 'to' ? 'mediator' : 'from';

        $keys = [
            $h = $side == 'to' ? $request['attr']['mediator_t_key'] : $request['attr']['from_key'],
            $b = $side == 'to' ? $request['attr']['to_key'] : $request['attr']['mediator_f_key'],
            $h != 'id' ? $h : $b
        ];

        return implode(
            self::glue(';'),
            self::isNewSyntax($keys)
                ? self::makeNewSyntax(self::key($request, $pair, $keys), $request['attr']["{$pair}_table"])
                : self::makeOldSyntax($request, $keys, $pair)
        );
    }

    private static function setLinesForDefaultOTX($request, $side)
    {
        $keys = [
            $f = $request['attr']['from_key'],
            $t = $request['attr']['to_key'],
            $f != 'id' ? $f: $t
        ];

        $localLine = self::setLocalLine($request['attr'], $side, $keys[0]);

        if (self::hasLocalLineOnly($side)) return $localLine;

        $foreignLine = self::setForeignLineForDefaultOTX($request, $keys, 'from');

        if (self::hasForeignLineOnly($side)) return $foreignLine;

        return implode(self::glue(), array_filter([$localLine, $foreignLine]));
    }

    private static function setForeignLineForDefaultOTX($request, $keys, $pair)
    {
        return implode(
            self::glue(';'),
            self::isNewSyntax($keys)
                ? self::makeNewSyntax(self::key($request, $pair, $keys), $request['attr']["{$pair}_table"])
                : self::makeOldSyntax($request, $keys, $pair)
        );
    }

    private static function makeNewSyntax(string $key, string $table): array
    {
        return [
            '$table->foreignId' .
            self::inject($key) .
            '->constrained' .
            self::inject($table)
        ];
    }

    private static function makeOldSyntax($request, $keys, $pair): array
    {
        $table = self::pairTable($request, $pair);

        return [
            self::oldSyntaxDeclaration($table, $keys),
            self::oldSyntaxDetails($table, $request['attr']["{$pair}_table"], $keys)
        ];
    }

    private static function oldSyntaxDeclaration($table, $keys)
    {
        return '$table->unsignedBigInteger' . self::foreignKey($table, $keys);
    }

    private static function foreignKey($table, $keys)
    {
        if (IsKeyFull::_($keys[1])) return self::inject($keys[1]);

        return self::inject("{$table}_" . ($keys[1] != 'id' ? $keys[1] : $keys[2]));
    }

    private static function oldSyntaxDetails($ref, $table, $keys)
    {
        return implode('', [
            '$table->foreign',
            self::foreignKey($ref, $keys),
            '->references',
            self::inject($keys[0]),
            '->on',
            self::inject($table)
        ]);
    }

    private static function setLocalLine($attr, $side, $key)
    {
        if ($attr['relation'] != 'mtm') {
            return $side == 'to' || in_array($key, ['', 'id']) ? '' : '$table->integer' . self::inject($key);
        }
    }

    private static function key($request, $side, $keys)
    {
        return self::pairTable($request, $side) . "_" . ($keys[0] ?: $keys[2]);
    }

    private static function pairTable($request, $side): string
    {
        return ConvertCase::snake($request['attr']["{$side}_table"], true);
    }

    private static function table($request, $side)
    {
    }

    private static function isNewSyntax($sides)
    {
        return array_reduce($sides, fn ($p, $c) => $p && in_array($c, ['', 'id']), true);
    }

    private static function inject($string)
    {
        return Text::inject($string, ['(', 'sq']);
    }

    private static function wrap($string)
    {
        return Text::wrap($string, 'sq');
    }

    private static function hasLocalLineOnly($side)
    {
        return $side == 'from';
    }

    private static function hasForeignLineOnly($side)
    {
        return $side == 'to';
    }

    private static function glue($start = '')
    {
        return $start . PHP_EOL . str_repeat(' ', 12);
    }

    private static function hasNoForeignKey(array $attr): bool
    {
        return $attr['is_mtm'] || $attr['is_through'] || $attr['polymorphic'];
    }

    private static function makeLocalKey($key)
    {
        return '$table->integer' . self::inject($key);
    }
}
