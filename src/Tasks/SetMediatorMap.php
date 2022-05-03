<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\HasMediatorModel;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Str;

class SetMediatorMap
{
    public static function _(array $attr, ?string $side = null): array
    {
        return $side ? [
            'mediator_keys' => $k = self::setKeys($attr, $side),
            'mediator_table' => self::setTable($attr, $k),
        ] : [
            'mediator' => Convention::method($attr['mediator'] ?? ''),
            'mediators' => Str::plural($attr['mediator']),
            'Mediator' => $m = Convention::class($attr['mediator'] ?? ''),
            'mediator_code' => self::setCode($attr, $m),
            'mediator_key' => self::setKey($attr),
        ];
    }

    private static function setKeys(array $attr, $side): string
    {
        if ($attr['relation'] != 'mtm') return '';

        $order = $side == 'From' ? ['to', 'from'] : ['from', 'to'];
        
        $keys = array_map(fn ($x) => $attr["{$x}_key"], $order);

        $keys[0] = $keys[1] && !$keys[0] ? "{$attr[$order[0]]}_id" : $keys[0];
        $keys[1] = $keys[0] && !$keys[1] ? "{$attr[$order[1]]}_id" : $keys[1];

        return Text::append(implode(', ', array_map(
            fn ($x) => Text::inject($x, "'"),
            array_filter($keys)
        )), ', ');
    }

    private static function setTable(array $attr, string $keys): string
    {
        if ($attr['relation'] != 'mtm') return '';

        return !HasMediatorModel::_($attr)
            && $attr['mediator_table'] != $attr['default_pivot_table']
            || $keys
            ? ', ' . Text::inject(ConvertCase::snake($attr['mediator_table']), "'")
            : '';
    }

    private static function setCode(array $attr, string $model): string
    {
        return HasMediatorModel::_($attr) ? "->using({$model}::class)" : '';
    }

    private static function setKey(array $attr): string
    {
        return Text::append(Text::inject($attr['mediator_key'] ?: ($attr['to_key'] ? ConvertCase::snake($attr['from']) . '_id' : ''
        ), "'"), ', ');
    }
}
