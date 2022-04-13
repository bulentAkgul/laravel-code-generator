<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Package;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;
use Bakgul\Kernel\Tasks\GenerateNamespace;
use Illuminate\Support\Facades\Artisan;

class CreateRequiredModel
{
    public static function model(string $package, string $model)
    {
        if (self::isModelExist($package, $model)) return;

        Artisan::call("create:file {$model} model" . self::appendVariation($model) . " $package");
    }

    private static function isModelExist(string $package, string $model): bool
    {
        return class_exists(GenerateNamespace::_([
            'root' => Package::root($package),
            'package' => $package,
            'family' => 'src'
        ], "Models\\{$model}"));
    }

    private static function appendVariation(string $model): string
    {
        return $model == 'Pivot' ? Text::append('pivot', Settings::seperators('modifier')) : '';
    }
}
