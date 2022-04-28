<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\FileContent\Tasks\Register;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Folder;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Pluralizer;
use Bakgul\Kernel\Helpers\Settings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HandlePivot
{
    public static function _(array $request)
    {
        if (!class_exists('\Bakgul\FileCreator\FileCreatorServiceProvider')) return;

        $request['attr']['pivot_model']
            ? self::withModel($request['attr'])
            : self::withoutModel($request['attr']);

        self::addColumns($request);
    }

    private static function withModel($attr)
    {
        Settings::set('files.migration.name_count', 'X');

        Artisan::call(implode(' ', array_filter([
            "create:file",
            $attr['pivot_model'],
            "model:pivot",
            $attr['pivot_package']
        ])));
    }

    private static function withoutModel($attr)
    {
        Settings::set('files.migration.pairs', ['']);
        Settings::set('files.migration.name_count', 'X');

        Artisan::call(implode(' ', array_filter([
            "create:file",
            $attr['pivot_table'],
            "migration",
            $attr['pivot_package']
        ])));
    }

    private static function addColumns($request)
    {
        $request['attr']['target_file'] = self::getMigration($request['attr']);

        if (!$request['attr']['target_file']) return;

        $request['map']['lines'] = self::makeCode($request);
        $request['map']['imports'] = '';

        Register::_($request, [], [
            'start' => ['$table->id()', 0],
            'end' => ['$table->timestamps()', 0],
            'part' => 'lines',
            'repeat' => 2,
            'isSortable' => false,
            'eol' => ';'
        ], 'lines', 'block');
    }

    private static function getMigration(array $attr): ?string
    {
        return Arry::get(array_filter(
            Folder::files(
                Path::package($attr['pivot_package']),
                Path::glue(['database', 'migrations'])
            ),
            fn ($x) => str_contains($x, $attr['pivot_table'])
        ), 0);
    }

    private static function makeCode(array $request)
    {
        return $request['attr']['polymorphic']
            ? [
                '$table->string("' . Str::singular($request['attr']['pivot_table']) . 'able_type")',
                '$table->integer("' . Str::singular($request['attr']['pivot_table']) . 'able_id")',
            ] : [
                '$table->foreignId("' . self::key($request['map'], 'from') . '")',
                '$table->foreignId("' . self::key($request['map'], 'to') . '")',
            ];
    }

    private static function key(array $map, string $key)
    {
        return $map['from_key'] ?: "{$map[$key]}_id";
    }
}
