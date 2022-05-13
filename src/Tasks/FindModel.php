<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;

class FindModel
{
    public static function _($name)
    {
        $model = self::getModel($name, [Settings::folders('packages')]);

        if ($model) return $model;
        
        $model = self::getModel($name, ['app', 'Models']);

        if ($model) return $model;
        
        $model = self::getModel($name, ['src', 'Models']);

        return $model;
    }

    private static function getModel(string $name, array $folders): string
    {
        return Arry::get(array_filter(
            Folder::files(self::path($folders)),
            fn ($x) => str_contains($x, Path::glue(['Models', "{$name}.php"]))
        ), 0) ?? '';
    }

    private static function path(array $folders): string
    {
        return Path::glue([base_path(), ...$folders]);
    }
}
