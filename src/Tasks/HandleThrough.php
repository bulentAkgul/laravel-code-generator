<?php

namespace Bakgul\CodeGenerator\Tasks;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HandleThrough
{
    public static function _(array $request)
    {
        self::createMediators($request['attr']);

        self::addColumns($request);
    }

    private static function createMediators(array $attr)
    {
        Artisan::call(implode(' ', array_filter([
            "create:file",
            $attr['mediator'],
            "model",
            $attr['mediator_package']
        ])));
    }

    private static function addColumns(array $request)
    {
        array_map(fn ($x) => self::addColumn($request, $x), ['mediator', 'to']);
    }

    private static function addColumn(array $request, string $key)
    {
        $request['attr']['target_file'] = FindMigration::_($request, [$key, Str::plural($key)]);

        if (!$request['attr']['target_file']) return;

        $request['map']['lines'] = MakeMigrationLine::_($request, $key);

        InsertForeignKey::_($request);
    }
}
