<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Settings;
use Illuminate\Support\Facades\Artisan;

class HandlePivot
{
    public static function _(array $request)
    {
        return;
        if (!class_exists('\Bakgul\FileCreator\FileCreatorServiceProvider')) return;

        // if pivot migration only
        Settings::set('files.migration.pairs', ['']);
        Artisan::call('create:file {table} migration {package}');
        // if pivot migration with model
        Artisan::call('create:file {name} model:pivot {package}');

        self::addColumns($request);
    }

    private static function addColumns($request)
    {
        // get migration file path.
        // insert relation command column lines.
    }
}
