<?php

namespace Bakgul\CodeGenerator\Tasks;

class SetPolymorphicMap
{
    public static function _(array $attr): array
    {
        return [
            'able' => "{$attr['to']}able"
        ];
    }
}