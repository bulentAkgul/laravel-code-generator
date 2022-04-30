<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\FileContent\Tasks\Register;

class InsertForeignKey
{
    public static function _(array $request)
    {
        Register::_($request, [], [
            'start' => ['})', -1],
            'end' => ['})', 0],
            'part' => 'lines',
            'repeat' => 2,
            'isSortable' => false,
            'eol' => ';'
        ], 'lines', 'block');
    }
}