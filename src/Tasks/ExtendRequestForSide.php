<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\HasMediatorModel;
use Bakgul\Kernel\Helpers\Package;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Tasks\GenerateNamespace;
use Illuminate\Support\Str;

class ExtendRequestForSide
{
    public static function model(array $request, string $side): array
    {
        return [
            'attr' => array_merge($request['attr'], [
                'stub' => self::setStub($request['attr'], $side),
                'target_file' => self::setTargetModel($request, $side)
            ]),
            'map' => array_merge($request['map'], [
                'to_key' => self::setToKey($request),
                'imports' => self::setImports($request, $side),
                ...SetMediatorMap::_($request['attr'], $side)
            ])
        ];
    }

    private static function setToKey(array $request): string
    {
        if ($request['map']['to_key']) return $request['map']['to_key'];

        if ($request['attr']['is_through']) return '';

        $key = $request['map']['from_key'] ? "{$request['map']['from']}_id" : '';

        return Text::append(Text::wrap($key, 'sq'), ', ');
    }

    private static function setImports(array $request, string $side): string
    {
        if (Settings::standalone()) return '';

        return implode(PHP_EOL, array_map(fn ($x) => "use {$x};", self::setUses($request, $side)));
    }

    private static function setUses(array $request, string $side): array
    {
        $uses = $side == 'From'
            ? self::fromUses($request)
            : self::toUses($request);

        return array_filter(self::addMediator($request, $uses, $side));
    }

    private static function fromUses(array $request): array
    {
        return [self::isSameNamespace($request) ? '' : self::namespace($request, 'to')];
    }

    private static function isSameNamespace($request, $sides = ['from', 'to']): bool
    {
        return $request['attr']["{$sides[0]}_package"] == $request['attr']["{$sides[1]}_package"];
    }

    private static function toUses(array $request): array
    {
        return [self::isSameNamespace($request) || (
            $request['attr']['relation'] != 'mtm' && $request['attr']['polymorphic']
        ) ? '' : self::namespace($request, 'from')];
    }

    private static function addMediator(array $request, array $uses, string $side): array
    {
        if (self::isSameNamespace($request, [strtolower($side), 'mediator'])) return $uses;

        if (
            $request['attr']['relation'] == 'mtm'
            || strtolower($side) == 'from' && $request['attr']['is_through']
        ) {
            return [...$uses, self::namespace($request, 'mediator')];
        }

        return $uses;
    }

    private static function namespace(array $request, string $side): string
    {
        return GenerateNamespace::_([
            ...$request['attr'],
            'root' => Package::root($request['attr']["{$side}_package"]),
            'package' => $request['attr']["{$side}_package"],
            'family' => 'src'
        ], ['Models', $request['map'][ucfirst($side)]]);
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
