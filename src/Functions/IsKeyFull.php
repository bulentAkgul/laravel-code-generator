<?php

namespace Bakgul\CodeGenerator\Functions;

class IsKeyFull
{
    public static function _(string $key): bool
    {
        return substr($key, -3) == '_id';
    }
}
