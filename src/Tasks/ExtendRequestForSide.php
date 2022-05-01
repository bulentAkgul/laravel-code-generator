<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Illuminate\Support\Str;

class ExtendRequestForSide
{
    public static function method(array $request, string $side): array
    {
        return [
            'attr' => array_merge($request['attr'], [
                'stub' => self::setStub($request['attr'], $side),
                'target_file' => self::setTargetModel($request, $side)
            ]),
            'map' => array_merge($request['map'], [
                'to_key' => self::setToKey($request)
            ])
        ];
    }

    private static function setToKey(array $request): string
    {
        if ($request['map']['to_key']) return $request['map']['to_key'];
        
        if ($request['attr']['is_through']) return '';

        $key = $request['map']['from_key'] ? "{$request['map']['from']}_id" : '';

        return Text::append(Text::inject($key, "'"), ', ');
    }

    public static function foreignKey(array $request, string $side): array
    {
        return [
            'attr' => array_merge($request['attr'], [
                'target_file' => self::setTargetMigration($request, $side)
            ]),
            'map' => array_merge($request['map'], [
                'lines' => self::setLine($request, $side)
            ]),
        ];
    }

    private static function setStub(array $attr, string $side): string
    {
        return implode('.', array_filter([
            Settings::code("relations.types.{$attr['relation']}"),
            strtolower($side),
            $attr['polymorphic'] ? 'poly' : $attr['variation'],
            'stub'
        ]));
    }

    private static function setTargetModel(array $request, string $side): string
    {
        $path = Path::glue([
            Path::head($request['attr'][strtolower($side) . "_package"], 'src'),
            'Models',
            "{$request['map'][$side]}.php"
        ]);

        if (file_exists($path)) return $path;
        
        return FindModel::_($request['map'][$side]);
    }

    private static function setTargetMigration(array $request, string $side): string
    {
        return FindMigration::_($request, [$side, Str::plural($side)]);
    }

    private static function setLine(array $request, string $side): array
    {
        return (array) MakeMigrationLine::_($request, $side);
    }
}
