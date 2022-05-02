<?php

namespace Bakgul\CodeGenerator\Functions;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Tasks\ConvertCase;
use Illuminate\Support\Str;

class SetDefaultPivot
{
    public static function _(array $parts)
    {
        return [
            'table' => $t = ConvertCase::snake(
                implode('-', Arry::sort(array_map(
                    fn ($x) => Str::singular($x),
                    $parts
                )))
            ),
            'model' => ConvertCase::pascal($t)
        ];
    }
}
