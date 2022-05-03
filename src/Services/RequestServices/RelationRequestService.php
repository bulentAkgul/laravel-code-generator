<?php

namespace Bakgul\CodeGenerator\Services\RequestServices;

use Bakgul\CodeGenerator\CodeGenerator;
use Bakgul\CodeGenerator\Tasks\FindPackage;
use Bakgul\CodeGenerator\Tasks\SetMediatorAttr;
use Bakgul\CodeGenerator\Tasks\SetMediatorMap;
use Bakgul\CodeGenerator\Tasks\SetPolymorphicMap;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Helpers\Convention;

class RelationRequestService extends CodeGenerator
{
    public static function handle(array $request): array
    {
        return self::extend([
            'attr' => $a = self::generateAttr($request),
            'map' => self::generateMap($a)
        ]);
    }

    private static function generateAttr(array $request): array
    {
        return [
            ...self::setBases($request),
            ...self::setSides($request['from'], 'from'),
            ...self::setSides($request['to'], 'to'),
        ];
    }

    private static function setBases(array $request): array
    {
        return [
            ...$request,
            'job' => 'relation',
            'relation' => $r = self::setRelation($request['relation']),
            'is_mtm' => $i = self::isManyToMany($r),
            'is_through' => self::isThrough($request, $i),
            'variation' => $r != 'mtm' && $request['mediator'] ? 'through' : '',
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

    private static function setSides(string $value, string $key): array
    {
        return array_combine([$key, "{$key}_key", "{$key}_package"], self::isolateParts($value));
    }

    private static function isolateParts(string $value)
    {
        return [self::setBase($value), self::setModifier($value), self::setPackage($value)];
    }

    private static function setBase(string $value)
    {
        return Isolation::name($value);
    }

    private static function setModifier(?string $value, string $alt = '')
    {
        return Isolation::variation($value ?? '', false) ?: $alt;
    }

    private static function setPackage(?string $value): string
    {
        return Settings::standalone() ? '' : (
            Isolation::subs($value ?? '') ?: FindPackage::_($value)
        );
    }

    private static function generateMap(array $attr): array
    {
        return ['imports' => '', ...self::setFroms($attr), ...self::setTos($attr)];
    }

    private static function setFroms(array $attr): array
    {
        return [
            'From' => Convention::class($attr['from']),
            'from' => Convention::method($attr['from'], true),
            'froms' => Convention::method($attr['from'], false),
            'from_key' => self::setKey($attr['from_key'])
        ];
    }

    private static function setTos(array $attr): array
    {
        return [
            'To' => Convention::class($attr['to']),
            'to' => Convention::method($attr['to'], true),
            'tos' => Convention::method($attr['to'], false),
            'to_key' => self::setKey($attr['to_key']),
        ];
    }

    private static function setKey(string $key)
    {
        return $key ? ', ' . Text::wrap(ConvertCase::snake($key), 'sq') : '';
    }

    private static function extend(array $request)
    {
        return [
            'attr' => $a = self::extendAttr($request['attr']),
            'map' => [...$request['map'], ...self::extendMap($a)],
        ];
    }

    private static function extendAttr(array $attr): array
    {
        return [
            ...$attr,
            ...SetMediatorAttr::_($attr),
        ];
    }

    private static function extendMap(array $attr): array
    {
        return [...SetMediatorMap::_($attr), ...SetPolymorphicMap::_($attr)];
    }
}
