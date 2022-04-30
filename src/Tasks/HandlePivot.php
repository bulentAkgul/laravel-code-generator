<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HandlePivot
{
    public static function _(array $request)
    {
        if (!class_exists('\Bakgul\FileCreator\FileCreatorServiceProvider')) return;

        $request['attr']['mediator']
            ? self::withModel($request['attr'])
            : self::withoutModel($request['attr']);

        self::addColumns($request);
    }

    private static function withModel($attr)
    {
        if (self::modelExists($attr)) return self::withoutModel($attr);

        Settings::set('files.migration.name_count', 'X');
        
        Artisan::call(implode(' ', array_filter([
            "create:file",
            $attr['mediator'],
            "model:pivot",
            $attr['mediator_package']
        ])));
    }

    private static function modelExists(array $attr)
    {
        return file_exists(Path::glue([
            Path::head($attr['mediator_package'], 'src'),
            'Models',
            Convention::class($attr['mediator']) . '.php'
        ]));
    }

    private static function withoutModel($attr)
    {
        Settings::set('files.migration.pairs', ['']);
        Settings::set('files.migration.name_count', 'X');

        Artisan::call(implode(' ', array_filter([
            "create:file",
            $attr['mediator_table'],
            "migration",
            $attr['mediator_package']
        ])));
    }

    private static function addColumns($request)
    {
        $request['attr']['target_file'] = self::getMigration($request['attr']);

        $request['map']['lines'] = self::makeCode($request);

        InsertForeignKey::_($request);
    }

    private static function getMigration(array $attr): ?string
    {
        return Arry::get(array_filter(
            Folder::files(
                Path::package($attr['mediator_package']),
                Path::glue(['database', 'migrations'])
            ),
            fn ($x) => str_contains($x, $attr['mediator_table'])
        ), 0);
    }

    private static function makeCode(array $request)
    {
        return $request['attr']['polymorphic']
            ? HandlePolymorphy::_($request, true)
            : self::setForeingKeys($request);
    }

    private static function setForeingKeys(array $request): array
    {
        return array_map(fn ($x) => self::setLine($request, $x), ['from', 'to']);
    }

    private static function setLine(array $request, string $key): string
    {
        return '$table->foreignId("'
            . self::key($request, $key)
            . '")->constrained('
            . Text::inject($request['map'][Str::plural($key)], "'")
            . ');';
    }

    private static function key(array $request, string $key)
    {
        return $request['attr']['from_key'] ?: "{$request['map'][$key]}_id";
    }
}
