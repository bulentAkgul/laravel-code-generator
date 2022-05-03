<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Text;

class MakeMigrationLine
{
    public static function _(array $request, string $key): string
    {
        return '$table->foreignId'
            . Text::inject(self::key($request, $key), ['(', 'sq'])
            . '->constrained'
            . Text::inject(self::table($request, $key), ['(', 'sq']);
    }

    private static function key(array $request, string $key): string
    {
        if ($request['attr']['relation'] == 'mtm') {
            return $request['attr']["{$key}_key"] ?: "{$request['map'][$key]}_id";
        }

        if ($key == 'mediator') return "{$request['map']['from']}_id";

        return $request['map']['to_key']
            ? trim($request['map']['to_key'], " ,'")
            : ($request['attr']['is_through']
                ? "{$request['map']['mediator']}_id"
                : "{$request['map']['from']}_id"
            );
    }

    private static function table(array $request, string $key): string
    {
        if ($request['attr']['relation'] == 'mtm') return Convention::table($request['map'][$key]);

        return Convention::table(
            $request['attr']['relation'] == 'mtm'
                ? $request['map'][$key]
                : ($key == 'mediator' || ($key == 'to' && !$request['attr']['is_through'])
                    ? $request['map']['from']
                    : $request['map']['mediator'])
        );
    }
}
