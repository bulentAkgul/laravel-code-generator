<?php

namespace Bakgul\CodeGenerator\Functions;

use Bakgul\Kernel\Helpers\Convention;

class SetMapSides
{
    public static function _(array $attr, string|array $sides): array
    {
        $output = [];

        if ($sides == 'mediator' && !$attr['has_mediator']) return [$output];

        foreach ((array) $sides as $side) {
            $src = $attr["{$side}_model"] ?: $attr["{$side}_table"];

            $output[] = [
                ucfirst($side) => Convention::class($src),
                $side => Convention::method($src, true),
                "{$side}s" => Convention::method($src, false),
            ];
        }

        return $output;
    }
}
