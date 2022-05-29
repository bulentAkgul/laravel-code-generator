<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\FileContent\Tasks\Register;

class InsertCode
{
    public static function key(array $request)
    {
        Register::_($request, [], [
            ...self::defaults(),
            'start' => ['})', -1],
            'end' => ['})', 0],
        ], 'lines', 'block');
    }

    public static function table($request)
    {
        Register::_($request, [], [
            ...self::defaults(),
            'start' => ['class', 1],
            'end' => ['}', -1],
            'isStrict' => true,
            'repeat' => 0,
        ], 'lines', 'block');
    }

    public static function uses($request)
    {
        Register::_($request, [
            'end' => ['class', 0],
            'findEndBy' => 'use',
        ], [], 'imports', 'line');
    }

    private static function defaults()
    {
        return  [
            'part' => 'lines',
            'repeat' => 2,
            'isSortable' => false,
            'eol' => ';',
            'jump' => ''
        ];
    }
}