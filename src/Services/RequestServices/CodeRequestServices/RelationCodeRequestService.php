<?php

namespace Bakgul\CodeGenerator\Services\RequestServices\CodeRequestServices;

use Bakgul\CodeGenerator\Services\RequestServices\CodeRequestService;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Tasks\MutateStub;
use Bakgul\Kernel\Helpers\Convention;

class RelationCodeRequestService extends CodeRequestService
{
    public static function handle(array $request): array
    {
        return [
            'attr' => $a = self::generateAttr($request),
            'map' => self::generateMap($a)
        ];
    }

    private static function generateAttr(array $request): array
    {
        return array_merge(
            $request,
            self::setBases($request['relation']),
            $f = self::setSides($request['from'], 'from'),
            $t = self::setSides($request['to'], 'to', $request['from']),
            self::setPivot($request, $f, $t),
        );
    }

    private static function setBases(string $relation)
    {
        [$relation, $variation] = self::isolateParts($relation);

        return [
            'job' => 'relation',
            'relation' => $r = self::setRelationType($relation),
            'variation' => $v = self::setVariation($variation),
            'stub' => self::setStub($r, $v)
        ];
    }

    private static function setRelationType(string $relation): string
    {
        return Arry::get(Settings::code('relations.types'), $relation) ?? $relation;
    }

    private static function setVariation(string $variation): string
    {
        return $variation ? Arry::get(Settings::code('relations.variations'), $variation) ?? $variation : '';
    }

    protected static function setStub(string $relation, string $variation): string
    {
        return MutateStub::set($relation, $variation);
    }

    private static function setSides(string $value, string $key, string $from = ''): array
    {
        return array_combine([$key, "{$key}_key", "{$key}_package"], self::isolateParts($value, $from));
    }

    private static function setPivot(array $request, array $from, array $to): array
    {
        $keys = ['pivot_model', 'pivot_table', 'pivot_package'];

        if (self::cannotHavePivot($request['relation'])) return Arry::combine($keys, default: '');

        $table = self::setPivotTable($request, $from['from'], $to['to']);

        return Arry::combine($keys, [
            self::setPivotModel($request['pivot'], $table),
            ConvertCase::snake($table),
            self::setPackage($request['pivot']) ?: $from['from_package']
        ]);
    }

    private static function cannotHavePivot(string $relation): bool
    {
        return Text::containsNone($relation, ['mtm', 'many_to_many']);
    }

    private static function setPivotTable(array $request, string $from, string $to): string
    {
        return self::hasDefaultPivot($request['pivot'])
            ? self::makePivotName($from, $to)
            : (self::setModifier($request['pivot']) ?: $request['pivot']);
    }

    private static function hasDefaultPivot(?string $pivot): bool
    {
        return $pivot == null || in_array($pivot, ['t', 'y', 'true', 'yes']);
    }

    private static function makePivotName(string $from, string $to): string
    {
        return implode('-', Arry::sort([$from, $to]));
    }

    private static function setPivotModel(?string $pivot, string $table)
    {
        if ($pivot == null) return '';

        if (self::hasDefaultPivot($pivot)) return $table;

        return self::setBase($pivot);
    }

    private static function generateMap(array $attr): array
    {
        return array_merge(
            self::setFroms($attr),
            self::setTos($attr),
            self::setPivots($attr)
        );
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

    private static function setKey(string $key)
    {
        return $key ? ', ' . Text::inject(ConvertCase::snake($key), "'") : '';
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

    private static function setPivots(array $attr): array
    {
        return [
            'Pivot' => $m = Convention::class($attr['pivot_model']),
            'pivot_table' => self::setPivotTableName($attr),
            'pivot_code' => self::setPivotCode($attr, $m),
        ];
    }

    private static function setPivotTableName(array $attr): string
    {
        return $attr['pivot_table'] && !$attr['pivot_model'] || $attr['from_key']
            ? ', ' . Text::inject(ConvertCase::snake($attr['pivot_table']), "'")
            : '';
    }

    private static function setPivotCode(array $attr, string $model): string
    {
        return $attr['pivot_model'] != $attr['pivot_table'] && $model
            ? "->using({$model}::class)"
            : '';
    }

    private static function isolateParts(string $value, string $from = '')
    {
        return [self::setBase($value), self::setModifier($value), self::setPackage($value, $from)];
    }

    private static function setBase(string $value)
    {
        return Isolation::name($value);
    }

    private static function setModifier(?string $value, string $alt = '')
    {
        return Isolation::variation($value ?? '', false) ?: $alt;
    }

    private static function setPackage(?string $value, string $from = ''): string
    {
        return Isolation::subs($value ?? '') ?: Isolation::subs($from) ?: '';
    }

    public static function modelCode(array $request, string $modelKey)
    {
        return [
            'attr' => array_merge($request['attr'], [
                'stub' => self::updateStub($request['attr']['stub'], $modelKey),
                'target_file' => self::setTargetFile($request, $modelKey)
            ]),
            'map' => $request['map']
        ];
    }

    private static function updateStub(string $stub, string $modelKey): string
    {
        return str_replace('.stub', '.' . strtolower($modelKey) . '.stub', $stub);
    }

    private static function setTargetFile(array $request, string $modelKey)
    {
        return Path::head($request['attr'][strtolower($modelKey) . "_package"] ?: $request['attr']["from_package"], 'src')
            . Text::append(Path::glue(['Models', "{$request['map'][$modelKey]}.php"]));
    }
}
