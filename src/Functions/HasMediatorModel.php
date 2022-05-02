<?php

namespace Bakgul\CodeGenerator\Functions;

use Bakgul\Kernel\Tasks\ConvertCase;

class HasMediatorModel
{
    public static function _(array $attr): bool
    {
        return $attr['model']
            || $attr['mediator']
            && ConvertCase::snake($attr['mediator']) != $attr['mediator_table'];
    }
}
