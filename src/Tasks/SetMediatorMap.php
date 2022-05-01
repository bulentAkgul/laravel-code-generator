<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Str;

class SetMediatorMap
{
    public static function _(array $attr): array
    {
        return [
            'mediator' => Convention::method($attr['mediator'] ?? ''),
            'mediators' => Str::plural($attr['mediator']),
            'Mediator' => $m = Convention::class($attr['mediator'] ?? ''),
            'mediator_table' => self::setTable($attr),
            'mediator_code' => self::setCode($m),
            'mediator_key' => self::setKey($attr)
        ];
    }

    private static function setTable(array $attr): string
    {
        return $attr['mediator_table'] && !$attr['mediator'] || $attr['from_key']
            ? ', ' . Text::inject(ConvertCase::snake($attr['mediator_table']), "'")
            : '';
    }

    private static function setCode(string $model): string
    {
        return $model ? "->using({$model}::class)" : '';
    }

    private static function setKey(array $attr): string
    {
        return Text::append(Text::inject($attr['mediator_key'] ?: (
            $attr['to_key'] ? ConvertCase::snake($attr['from']) . '_id' : ''
        ), "'"), ', ');
    }
}