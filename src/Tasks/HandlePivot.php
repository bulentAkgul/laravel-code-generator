<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\HasPivotModel;
use Bakgul\Kernel\Functions\CreateFileRequest;
use Bakgul\Kernel\Tasks\SimulateArtisanCall;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;

class HandlePivot
{
    public static function _(array $request)
    {
        if (!class_exists('\Bakgul\FileCreator\FileCreatorServiceProvider')) return;

        self::createModel($request);

        self::createMigration($request);
    }

    private static function createModel(array $request)
    {
        if (self::isNotModelable($request['attr']) || self::modelExists($request['attr'])) return;
        
        self::dropMigration();

        (new SimulateArtisanCall)(CreateFileRequest::_([
            'name' => $request['attr']['mediator_model'],
            'type' => 'model:pivot',
            'package' => $request['attr']['mediator_package'],
        ]));

        self::addTable($request);
    }

    private static function isNotModelable(array $attr): bool
    {
        return !HasPivotModel::_($attr);
    }

    private static function modelExists(array $attr)
    {
        return file_exists(Path::glue([
            Path::head($attr['mediator_package'], 'src'),
            'Models',
            Convention::class($attr['mediator_model']) . '.php'
        ]));
    }

    private static function dropMigration()
    {
        Settings::set('files.model.pairs', Settings::files('model.pairs', fn ($x) => $x != 'migration'));
    }

    private static function addTable(array $request)
    {
        $request['attr']['target_file'] = self::getModel($request);

        $request['map']['lines'] = 'protected $table = ' . Text::wrap($request['attr']['mediator_table'], 'sq');

        InsertCode::table($request);
    }

    private static function createMigration(array $request)
    {
        Settings::set('files.migration.pairs', ['']);
        Settings::set('files.migration.name_count', 'X');

        (new SimulateArtisanCall)(CreateFileRequest::_([
            'name' => $request['attr']['mediator_table'],
            'type' => 'migration',
            'package' => $request['attr']['mediator_package'],
        ]));

        self::addColumns($request);
    }

    private static function addColumns($request)
    {
        $request['attr']['target_file'] = self::getMigration($request['attr']);

        $request['map']['lines'] = self::makeCode($request);

        InsertCode::key($request);
    }

    private static function getModel(array $request): ?string
    {
        return Arry::get(array_filter(array_merge(
            Folder::files(Path::package($request['attr']['mediator_package'])),
            Folder::files(Path::head(family: 'src'))
        ), fn ($x) => str_contains($x, "{$request['map']['Mediator']}.php")), 0);
    }

    private static function getMigration(array $attr): ?string
    {
        return Arry::get(array_filter(array_merge(
            Folder::files(Path::package($attr['mediator_package'])),
            Folder::files(Path::glue([base_path(), 'database', 'migrations']))
        ), fn ($x) => str_contains($x, "create_{$attr['mediator_table']}_table")), 0);
    }

    private static function makeCode(array $request)
    {
        return $request['attr']['polymorphic']
            ? HandlePolymorphy::_($request, true)
            : self::setForeingKeys($request);
    }

    private static function setForeingKeys(array $request): string
    {
        return SetMigrationLine::_($request, 'mediator');
    }
}
