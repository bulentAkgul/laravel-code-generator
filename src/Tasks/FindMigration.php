<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Package;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;

class FindMigration
{
    public static function _(array $request, array $keys = ['to', 'tos'])
    {
        $names = self::setNames($request['map'], $keys);

        $migration = self::getMigration($names, self::setTail($request['attr'], $keys[0]));

        if ($migration) return $migration;

        $migration = self::getMigration($names, ['database', 'migrations']);

        if ($migration) return $migration;

        return '';
    }

    private static function setNames(array $map, array $keys): array
    {
        return array_map(fn ($x) => "create_{$map[$x]}_table.php",  $keys);
    }

    private static function setTail(array $attr, string $key)
    {
        $key = $key == 'able' ? ($attr['is_mtm'] ? 'mediator' : 'to') : $key;

        return array_filter([
            Settings::folders('packages'),
            Package::root($attr["{$key}_package"]),
            $attr["{$key}_package"]
        ]);
    }

    private static function getMigration(array $names, array $folders): string
    {
        foreach ($names as $name) {
            $path = Arry::get(array_filter(
                Folder::files(self::path($folders)),
                fn ($x) => str_contains($x, $name)
            ), 0);

            if ($path) return $path;
        }

        return '';
    }

    private static function path(array $folders): string
    {
        return Path::glue([base_path(), ...$folders]);
    }
}
