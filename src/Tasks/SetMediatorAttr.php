<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\CodeGenerator\Functions\SetSideKeys;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Isolation;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\ConvertCase;

class SetMediatorAttr
{
    public static function _(array $attr): array
    {
        return [...self::setKeys($attr), ...self::defaults($attr)];
    }

    private static function setKeys($attr)
    {
        return match (true) {
            $attr['is_through'] => self::setThrough($attr),
            $attr['is_mtm'] => self::setPivot($attr),
            default => []
        };
    }

    private static function defaults(array $attr): array
    {
        $name = self::makePivot($attr);

        return [
            'default_pivot_table' => Convention::table($name, true),
            'default_pivot_model' => Convention::class($name),
        ];
    }

    private static function setThrough(array $attr): array
    {
        $mediator = SplitSideInput::_(self::setInput($attr), 'mediator');

        $mediator['mediator_key'] = Isolation::variation($attr['mediator']);
        $mediator['mediator_f_key'] = Isolation::part($mediator['mediator_key'], 0, 'addition') ?: 'id';
        $mediator['mediator_t_key'] = Isolation::part($mediator['mediator_key'], 1, 'addition') ?: 'id';

        return $mediator;
    }

    private static function setInput(array $attr): string
    {
        if ($attr['polymorphic']) return '';

        if (Settings::standalone()) return $attr['mediator'];

        $parts = self::getParts($attr['mediator']);

        return Text::prepend($parts['package'] ?: $attr['from_package'])
            . $parts['table']
            . Text::append($parts['column'] ?: 'id.id', Settings::seperators('modifier'))
            . Text::append($parts['model'] ?: $parts['table'], Settings::seperators('modifier'));
    }

    private static function getParts($mediator)
    {
        return [
            'package' => Isolation::subs($mediator),
            'table' => Isolation::name($mediator),
            'column' => Isolation::variation($mediator),
            'model' => Isolation::extra($mediator)
        ];
    }

    private static function setPivot(array $attr): array
    {
        return Arry::combine(SetSideKeys::_('mediator'), [
            self::setPackage($attr),
            $t = self::setPivotTable($attr),
            '',
            self::setPivotModel($attr['mediator'], $attr['model'], $t),
        ]);
    }

    private static function setPackage(array $attr): string
    {
        return Isolation::subs($attr['mediator'] ?? '') ?: $attr['from_package'];
    }

    private static function setPivotTable(array $attr): string
    {
        return self::hasDefaultPivot($attr) ? self::makePivot($attr) : self::isolatePivot($attr);
    }

    private static function isolatePivot(array $attr): string
    {
        return ConvertCase::snake(Isolation::name($attr['mediator']));
    }

    private static function hasDefaultPivot(array $attr): bool
    {
        return !$attr['mediator']
            || in_array(Isolation::name($attr['mediator']), ['t', 'y', 'true', 'yes'])
            || ($attr['relation'] == 'mtm' && $attr['polymorphic']);
    }

    private static function makePivot(array $attr): string
    {
        return $attr['polymorphic']
            ? Convention::table($attr['to_table'], true) . "ables"
            : implode('_', Arry::sort(array_map(
                fn ($x) => Convention::table($x, true),
                [$attr['from_table'], $attr['to_table']]
            )));
    }

    private static function setPivotModel(?string $pivot, bool $model, string $table)
    {
        return !$pivot && !$model ? '' : self::setModelName($pivot, $table);
    }

    private static function setModelName(?string $pivot, string $table): string
    {
        $model = Isolation::variation($pivot ?? '') ?: $table;

        return Convention::class(in_array($model, ['t', 'y', 'true', 'yes']) ? $table : $model);
    }
}
