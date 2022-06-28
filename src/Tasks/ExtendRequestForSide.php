<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Package;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Tasks\FindModel;
use Bakgul\Kernel\Tasks\GenerateNamespace;

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
                'imports' => self::setImports($request, $side),
                ...ExtendMediatorMap::_($request['attr'], $side)
            ])
        ];
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
        return Arry::get($request['attr'], "{$sides[0]}_package")
            == Arry::get($request['attr'], "{$sides[1]}_package");
    }

    private static function toUses(array $request): array
    {
        return [self::isSameNamespace($request) || (
            $request['attr']['relation'] != 'mtm' && $request['attr']['polymorphic']
        ) ? '' : self::namespace($request, 'from')];
    }

    private static function addMediator(array $request, array $uses, string $side): array
    {
        if (self::isMediatorModelNotUsed($request, $side)) return $uses;

        if (
            $request['attr']['relation'] == 'mtm'
            || strtolower($side) == 'from' && $request['attr']['is_through']
        ) {
            return [...$uses, self::namespace($request, 'mediator')];
        }

        return $uses;
    }

    private static function isMediatorModelNotUsed($request, $side)
    {
        return !$request['attr']['has_mediator']
            || self::isSameNamespace($request, [strtolower($side), 'mediator']);
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

    public static function migration(array $request, string $side): array
    {
        return [
            'attr' => array_merge($request['attr'], [
                'target_file' => FindMigration::_($request, $side)
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

    private static function setLine(array $request, string $side): array
    {
        return (array) SetMigrationLine::_($request, $side);
    }
}
