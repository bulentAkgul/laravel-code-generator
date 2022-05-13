<?php

namespace Bakgul\CodeGenerator\Functions;

use Bakgul\Kernel\Helpers\Convention;

class SetPairTable
{
    public static function _(array $attr, string $side): string
    {
        $side = ($side == 'to'
            ? ($attr['is_through'] ? 'mediator' : 'from')
            : 'from');
            
        return Convention::table($attr["{$side}_table"], true);
    }
}
