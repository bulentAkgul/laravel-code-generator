<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\HasPivotModel;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class ExtendMediatorMap
{
    public static function _(array $attr, ?string $side = null): array
    {
        return [
            'mediator_code' => self::setCode($attr),
            'mediator_keys' => $k = self::setKeys($attr, $side),
            'mediator_table' => self::setTable($attr, $k),
        ];
    }

    private static function setCode(array $attr): string
    {
        return HasPivotModel::_($attr) ? "->using({$attr['mediator_model']}::class)" : '';
    }

    private static function setKeys(array $attr, $side): string
    {
        if ($attr['relation'] != 'mtm') return '';

        $order = $side == 'From' ? ['to', 'from'] : ['from', 'to'];

        $keys[0] = $attr["{$order[0]}_key"] == 'id' ? '' : self::setKey($attr, $order[0]);
        $keys[1] = $attr["{$order[1]}_key"] == 'id' && !$keys[0] ? '' : self::setKey($attr, $order[1]);

        return Text::append(implode(', ', array_map(
            fn ($x) => Text::wrap($x, 'sq'),
            array_filter(array_reverse($keys))
        )), ', ');
    }

    private static function setKey($attr, $side)
    {
        return ConvertCase::snake($attr["{$side}_table"], true) . '_' . $attr["{$side}_key"];
    }

    private static function setTable(array $attr, string $keys): string
    {
        if ($attr['relation'] != 'mtm') return '';

        return self::hasTable($attr, $keys)
            ? ', ' . Text::wrap(ConvertCase::snake($attr['mediator_table']), 'sq')
            : '';
    }

    private static function hasTable($attr, $keys)
    {
        return !HasPivotModel::_($attr)
            && $attr['mediator_table'] != $attr['default_pivot_table']
            || $keys;
    }
}
