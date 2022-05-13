<?php

namespace Bakgul\CodeGenerator\Tests\Concerns;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;

trait TestExecutions
{
    public function runOneTo(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', ''],
        array $uses = ['from' => [], 'to' => []],
        bool $isPoly = false
    ) {
        $this->init();

        [$from, $to, $modelDetails] = $this->prepareOneTo($names, $packages, $keys, $models);

        if ($this->scenario == 'pl') {
            $from['uses'] = Arry::get($uses, 'from') ?? [];
            $to['uses'] = Arry::get($uses, 'to') ?? [];
        }

        $this->create($this->args($this->specs([$from, $to], $keys, $packages, $models)) . $this->opts($isPoly));

        [$from, $to] = $this->fillSides($from, $to);

        $this->assertCase($from, $to, $modelDetails);
    }

    public function runHasThrough(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', ''],
        array $uses = [],
        bool $makeDir = false
    ) {
        $this->init();

        if ($this->scenario == 'pl' && $makeDir) mkdir(Path::glue([base_path(), Settings::folders('packages'), 'core', $packages[2]]));

        [$from, $to, $mediator, $models] = $this->prepareHasThrough($names, $packages, $keys, $models);

        if ($this->scenario == 'pl') $from['uses'] = $uses;

        $this->create($this->args($this->specs([$from, $to, $mediator], $keys, $packages)));

        [$from, $to, $mediator] = $this->fillSides($from, $to, $mediator);

        $this->assertCase($from, $to, $mediator, $models);
    }

    public function runManyToTest(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', ''],
        bool $isMediatorless = true,
        bool $hasModel = false,
        bool $isPoly = false,
    ) {
        $this->init('mtm');

        if ($isPoly) Settings::set('evaluator.evaluate_commands', false);

        [$from, $to, $mediator, $modelDetails] = $this->prepareManyTo($names, $packages, $keys, $models);

        foreach (['from', 'to', 'mediator'] as $side) {
            $$side['model'] = $modelDetails[$side]['name'];
        }

        $specs = $this->specs([$from, $to, $mediator], $keys, $packages, $models);

        if (!$names[2]) $specs[2] = '';

        $this->create($this->args(array_filter($specs)) . $this->opts($isPoly, $hasModel));

        [$from, $to] = $this->fillSides($from, $to);

        $modelDetails = $isMediatorless ? $this->deleteMediator($modelDetails) : $modelDetails;

        $this->assertCase($from, $to, $mediator, $modelDetails);
    }

    public function args(array $specs)
    {
        return implode(' ', $specs);
    }

    public function opts($isPoly = false, $hasModel = false)
    {
        return Text::append(implode(' ', array_filter([$isPoly ? '-p' : '', $hasModel ? '-m' : ''])), ' ');
    }

    public function deleteMediator($models)
    {
        return [...$models, 'mediator' => ['name' => '', 'package' => '', 'path' => '']];
    }
}
