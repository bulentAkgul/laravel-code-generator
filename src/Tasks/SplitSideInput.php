<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\SetSideKeys;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class SplitSideInput
{
    public static function _(string $input, string $side): array
    {
        return array_combine(SetSideKeys::_($side), self::isolateParts($input));
    }

    private static function isolateParts(string $input)
    {
        return [self::setPackage($input), ...self::setParts($input)];
    }

    private static function setParts(string $input): array
    {
        $input = Text::getTail($input, Settings::seperators('folder'));

        return array_map(fn ($x) => ConvertCase::snake(
            Isolation::part($input, $x)
        ), [0, 1, 2]);
    }

    private static function setPackage(?string $input): string
    {
        return  Settings::standalone() ? '' : (
            Isolation::subs($input ?? '') ?: FindPackage::_($input)
        );
    }
}
