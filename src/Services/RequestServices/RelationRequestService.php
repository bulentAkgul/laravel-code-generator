<?php

namespace Bakgul\CodeGenerator\Services\RequestServices;

use Bakgul\CodeGenerator\CodeGenerator;
use Bakgul\CodeGenerator\Functions\SetMapSides;
use Bakgul\CodeGenerator\Tasks\SetKeys;
use Bakgul\CodeGenerator\Tasks\SetMediatorAttr;
use Bakgul\CodeGenerator\Tasks\SetPolymorphicMap;
use Bakgul\CodeGenerator\Tasks\SplitSideInput;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Helpers\Convention;

class RelationRequestService extends CodeGenerator
{
    public static function handle(array $request): array
    {
        return self::modify(self::extend(self::generate($request)));
    }

    private static function generate(array $request): array
    {
        return [
            'attr' => self::generateAttr($request),
            'map' => self::generateMap()
        ];
    }

    private static function generateAttr(array $request): array
    {
        return [...self::setBases($request), ...self::setInputs($request)];
    }

    private static function setBases(array $request): array
    {
        return [
            ...$request,
            'job' => 'relation',
            'relation' => $r = self::setRelation($request['relation']),
            'is_mtm' => $i = self::isManyToMany($r),
            'is_through' => $f = self::isThrough($request, $i),
            'has_mediator' => $i || $f,
            'variation' => $r != 'mtm' && $request['mediator'] ? 'through' : '',
        ];
    }

    private static function setInputs($request)
    {
        return [
            ...SplitSideInput::_($request['from'], 'from'),
            ...SplitSideInput::_($request['to'], 'to'),
            ...SplitSideInput::_('', 'mediator'),
        ];
    }

    private static function isManyToMany(string $relation): bool
    {
        return Text::containsSome($relation, ['mtm', 'many_to_many']);
    }

    private static function isThrough(array $request, bool $isMTM): bool
    {
        return $request['mediator'] && !$request['polymorphic'] && !$isMTM;
    }

    private static function setRelation(string $relation): string
    {
        $relations = Settings::code('relations.types');

        return Arry::has($relation, $relations)
            ? $relation
            : Arry::get(Arry::find($relations, $relation) ?? [], 'key');
    }

    private static function setModels($attr)
    {
        return [
            'from_model' => Convention::class($attr['from_model'] ?: $attr['from_table']),
            'to_model' => Convention::class($attr['to_model'] ?: $attr['to_table']),
            'mediator_model' => Convention::class($attr['mediator_model']),
        ];
    }

    private static function generateMap(): array
    {
        return ['imports' => ''];
    }

    private static function extend(array $request)
    {
        return [
            'attr' => $a = self::extendAttr($request['attr']),
            'map' => self::extendMap($request['map'], $a),
        ];
    }

    private static function extendAttr(array $attr): array
    {
        return [...$attr, ...SetMediatorAttr::_($attr)];
    }

    private static function extendMap(array $map, array $attr): array
    {
        return array_merge($map, array_reduce(
            SetMapSides::_($attr, ['from', 'to',  'mediator']),
            fn ($p, $c) => [...$p, ...$c],
            []
        ));
    }

    private static function modify(array $request)
    {
        return [
            'attr' => $a = self::modifyAttr($request['attr']),
            'map' => self::modifyMap($request['map'], $a)
        ];
    }

    private static function modifyAttr($attr)
    {
        return array_merge($attr, self::setModels($attr), [
            'mediator_id' => $attr['to_key'],
            'from_key' => $attr['from_key'] ?: 'id',
            'to_key' => $attr['to_key'] ?: 'id',
        ]);
    }

    private static function modifyMap($map, $attr)
    {
        return [...$map, ...SetPolymorphicMap::_($attr), ...SetKeys::_($attr, $map)];
    }
}
