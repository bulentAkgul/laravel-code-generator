<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class MakeMigrationLine
{
    public static function _(array $request, string $key): string
    {
        return '$table->foreignId('
            . Text::inject(self::key($request, $key), "'")
            . ')'
            . '->constrained('
            . Text::inject(self::table($request, $key), "'")
            . ')';
    }

    private static function key(array $request, string $key): string
    {
        if ($key == 'mediator') return "{$request['map']['from']}_id";

        return $request['map']['to_key'] ?: ($request['attr']['is_through']
            ? "{$request['map']['mediator']}_id"
            : "{$request['map']['from']}_id"
        );
    }

    private static function table(array $request, string $key): string
    {
        return ConvertCase::snake(
            $key == 'mediator' || ($key == 'to' && !$request['attr']['is_through'])
                ? $request['map']['froms']
                : $request['map']['mediators']
        );
    }
}
