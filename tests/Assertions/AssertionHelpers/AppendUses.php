<?php

namespace Bakgul\CodeGenerator\Tests\Assertions\AssertionHelpers;

class AppendUses
{
    public static function _($uses, $add)
    {
        return ($add ? [4 => $uses[0]] : [])
             + ($add == 2 ? [5 => $uses[1]] : []);
    }
}