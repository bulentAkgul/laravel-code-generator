<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Tasks\ConvertCase;

class SetMediatorAttr
{
    private static $keys = ['mediator', 'mediator_table', 'mediator_package', 'mediator_key'];

    public static function _(array $attr): array
    {
        return [
            ...Arry::combine(self::$keys, self::setValues($attr), ''),
            ...self::defaults($attr),
        ];
    }

    private static function defaults(array $attr): array
    {
        $name = self::makePivotName($attr);

        return [
            'default_pivot_table' => Convention::table($name, true),
            'default_pivot_model' => Convention::class($name),
        ];
    }

    private static function setValues($attr): array
    {
        return match (true) {
            $attr['is_through'] => self::setThrough($attr),
            $attr['is_mtm'] => self::setPivot($attr),
            default => []
        };
    }

    private static function setThrough(array $attr): array
    {
        return $attr['polymorphic'] || !$attr['mediator'] ? [] : [
            Isolation::name($attr['mediator']),
            '',
            self::setPackage($attr),
            Isolation::variation($attr['mediator']),
        ];
    }

    private static function setPivot(array $attr): array
    {
        $table = self::setPivotTable($attr);

        return [
            self::setPivotModel($attr['mediator'], $attr['model'], $table),
            ConvertCase::snake($table),
            self::setPackage($attr),
            ''
        ];
    }

    private static function setPackage(array $attr): string
    {
        return Isolation::subs($attr['mediator'] ?? '') ?: $attr['from_package'];
    }

    private static function setPivotTable(array $attr): string
    {
        return self::hasDefaultPivot($attr['mediator'])
            ? self::makePivotName($attr)
            : Isolation::name($attr['mediator']);
    }

    private static function hasDefaultPivot(?string $pivot): bool
    {
        return !$pivot || in_array($pivot, ['t', 'y', 'true', 'yes']);
    }

    private static function makePivotName(array $attr): string
    {
        return $attr['polymorphic']
            ? "{$attr['to']}ables"
            : implode('-', Arry::sort([$attr['from'], $attr['to']]));
    }

    private static function setPivotModel(?string $pivot, bool $model, string $table)
    {
        return $pivot == null && !$model ? '' : self::setModelName($pivot, $table);
    }

    private static function setModelName(?string $pivot, string $table): string
    {
        $model = Isolation::variation($pivot ?? '') ?: $table;

        return in_array($model, ['t', 'y', 'true', 'yes']) ? $table : $model;
    }
}
