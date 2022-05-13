<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Helpers\Settings;

class FindPackage
{
    public static function _(string $value)
    {
        $model = explode(DIRECTORY_SEPARATOR, self::model($value));

        return $model ? self::package($model) : '';
    }

    private static function model(string $value)
    {
        return FindModel::_(Convention::class(Isolation::name($value)));
    }

    private static function package(array $model): string
    {
        return array_filter($model) ? $model[array_search(Settings::folders('packages'), $model) + 2] : '';
    }
}