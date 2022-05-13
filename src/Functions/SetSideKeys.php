<?php

namespace Bakgul\CodeGenerator\Functions;

class SetSideKeys
{
    public static function _(string $key): array
    {
        return array_map(fn ($x) => "{$key}_{$x}", ['package', 'table', 'key', 'model']);
    }
}