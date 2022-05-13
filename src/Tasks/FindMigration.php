<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Package;
use Bakgul\Kernel\Helpers\Path;

class FindMigration
{
    public static function _(array $request, string $key)
    {
        $table = $request['attr']["{$key}_table"];

        foreach (self::folders($request['attr'], $key) as $folder) {
            $migration = array_filter(
                Folder::files($folder),
                fn ($x) => str_contains($x, "create_{$table}_table")
            );

            if (array_filter($migration)) return Arry::get($migration, 0);
        }

        return '';
    }

    private static function folders(array $attr, string $key)
    {
        return array_filter([
            self::package($attr["{$key}_package"]),
            database_path('migrations')
        ]);
    }

    private static function package(string $package): string
    {
        return $package ? Package::path($package, Path::glue(['database', 'migrations'])) : '';
    }
}
