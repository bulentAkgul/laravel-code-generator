<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Tasks\ConvertCase;

class SetPolymorphicMap
{
    public static function _(array $attr): array
    {
        return [
            'able' => ConvertCase::snake($attr['to_model']) . 'able'
        ];
    }
}