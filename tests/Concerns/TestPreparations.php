<?php

namespace Bakgul\CodeGenerator\Tests\Concerns;

use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Helpers\Settings;
use Bakgul\Kernel\Helpers\Text;

trait TestPreparations
{
    public function prepareOneTo(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', '']
    ): array {
        return [
            $f = [...$this->names($names[0] ?: 'nice-posts', $models[0]), 'key' => $keys[0], 'uses' => []],
            $t = [...$this->names($names[1] ?: 'comments', $models[1]), 'key' => $keys[1], 'uses' => []],
            $this->setModels([$f['model'], $t['model'], ''], $packages),
        ];
    }

    public function prepareHasThrough(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', '']
    ): array {
        return [
            $f = [...$this->names($names[0] ?: 'used-posts', $models[0]), 'key' => $keys[0], 'uses' => []],
            $t = [...$this->names($names[1] ?: 'comments', $models[1]), 'key' => $keys[1], 'uses' => []],
            $m = [...$this->names($names[2] ?: 'images', $models[2]), 'key' => $keys[2], 'uses' => []],
            $this->setModels([$f['model'], $t['model'], $m['model']], $packages),
        ];
    }

    public function prepareManyTo(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        array $models = ['', '', ''],
    ): array {
        return [
            $f = [...$this->names($names[0] ?: 'posts'), 'key' => $keys[0], 'uses' => []],
            $t = [...$this->names($names[1] ?: 'comments'), 'key' => $keys[1], 'uses' => []],
            $m = [...$this->names($names[2] ?: $this->mediatorName($names)), 'key' => $keys[2], 'uses' => []],
            $this->setModels([$models[0] ?: $f['model'], $models[1] ?: $t['model'], $models[2] ?: $m['model']], $packages),
        ];
    }

    public function specs($specs, $keys = [], $packages = [], $models = []): array
    {
        $parts = [];

        foreach ($specs as $i => $details) {
            $parts[] = ''
                . Text::prepend(Arry::get($packages, $i) ?? '', Settings::seperators('folder'))
                . $details['passed']
                . Text::append(Arry::get($keys, $i) ?? '', Settings::seperators('modifier'))
                . Text::append(Arry::get($models, $i) ?? '', Settings::seperators('modifier'));
        }

        return $parts;
    }

    public function fillSides($from, $to, $mediator = [])
    {
        $from['side'] = 'from';
        $to['side'] = 'to';

        $from['key'] = $from['key'] ?: 'id';
        $to['key'] = $to['key'] ?: 'id';

        if ($mediator) {
            $mediator['side'] = 'mediator';
            $mediator['key'] = $this->mediatorKeys($mediator['key']);
        }

        return [$from, $to, $mediator];
    }
}
