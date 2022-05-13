<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Functions\CreateFileRequest;
use Bakgul\Kernel\Tasks\SimulateArtisanCall;

class HandleThrough
{
    public static function _(array $request)
    {
        self::createMediators($request['attr']);

        self::addMigrationLines($request);
    }

    private static function createMediators(array $attr)
    {
        (new SimulateArtisanCall)(CreateFileRequest::_([
            'name' => $attr['mediator'],
            'type' => 'model',
            'package' => $attr['mediator_package'],
        ]));
    }

    private static function addMigrationLines(array $request)
    {
        $request['attr']['target_file'] = FindMigration::_($request, 'mediator');

        if (!$request['attr']['target_file']) return;

        $request['map']['lines'] = SetMigrationLine::_($request, 'mediator');

        if ($request['map']['lines']) InsertCode::key($request);
    }
}
