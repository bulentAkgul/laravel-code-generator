<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Functions\CreateFileRequest;
use Bakgul\Kernel\Tasks\SimulateArtisanCall;
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
        (new SimulateArtisanCall)(CreateFileRequest::_([
            'name' => $attr['mediator'],
            'type' => 'model',
            'package' => $attr['mediator_package'],
        ]));
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

        InsertCode::key($request);
    }
}
