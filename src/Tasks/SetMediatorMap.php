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
            'mediator_table' => self::setMediatorTable($attr),
            'mediator_code' => self::setMediatorCode($attr, $m),
        ];
    }

    private static function setMediatorTable(array $attr): string
    {
        return $attr['mediator_table'] && !$attr['mediator'] || $attr['from_key']
            ? ', ' . Text::inject(ConvertCase::snake($attr['mediator_table']), "'")
            : '';
    }

    private static function setMediatorCode(array $attr, string $model): string
    {
        return $model && $attr['mediator'] != $attr['mediator_table']
        ? "->using({$model}::class)"
        : '';
    }
}