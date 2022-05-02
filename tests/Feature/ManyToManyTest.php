<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\Assertions\ManyToManyAssertion;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Tasks\ConvertCase;

class ManyToManyTest extends TestCase
{
    use ManyToManyAssertion;

    /** @test */
    public function mm_without_pivot_without_model_without_package()
    {
        $this->mode = 'mtm';

        foreach (['sl', 'sp', 'pl'] as $isAlone) {
            $this->setupTest($isAlone);

            [$from, $to, $mediator, $models] = $this->prepare();

            if ($isAlone == 'pl') {}

            $this->create("{$from[0]} {$to[0]}");

            $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
        }
    }

    private function prepare(array $names = ['', '', ''], array $packages = ['', '', ''], array $keys = ['', '', '']): array
    {
        return [
            $f = [...$this->names($names[0] ?: 'post'), $keys[0], []],
            $t = [...$this->names($names[1] ?: 'comment'), $keys[1], []],
            $m = [...$this->names($names[2] ?: $this->mediatorName($names)), $keys[2], []],
            $this->setModels([$f[2], $t[2], $m[2]], $packages),
        ];
    }

    private function mediatorName($names)
    {
        return  implode('-', array_map(
            fn ($x) => ConvertCase::kebab($x, true),
            Arry::sort([$names[0] ?: 'post', $names[1] ?: 'comment'])
        ));
    }

    private function deleteMediator($models)
    {
        return [...$models, 'mediator' => ['', '', '']];
    }
}
