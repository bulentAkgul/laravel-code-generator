<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\ManyToPolymorphicAssertion;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Settings;

class ManyToPolymorphicTest extends TestCase
{
    use ManyToPolymorphicAssertion;

    /** @test */
    public function mp_without_pivot_without_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare();

        $this->create("{$from[0]} {$to[0]} -p");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mp_without_pivot_with_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare();

        $this->create("{$from[0]} {$to[0]} -p -m");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mp_with_custom_pivot_without_model_without_keys_without_package()
    {
        Settings::set('evaluator.evaluate_commands', false);

        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare();

        $this->create("{$from[0]} {$to[0]} images -p");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mp_with_custom_pivot_with_model_without_keys_without_package()
    {
        Settings::set('evaluator.evaluate_commands', false);

        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(['', '', 'commentable']);

        $this->create("{$from[0]} {$to[0]} images -p -m");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mp_without_pivot_without_model_with_keys_without_package()
    {
        // Settings::set('evaluator.evaluate_commands', false);

        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(keys: ['from_id', 'to_id', '']);

        $this->create("{$from[0]}:from_id {$to[0]}:to_id -p");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    private function prepare(
        array $names = ['', '', ''],
        array $packages = ['', '', ''],
        array $keys = ['', '', ''],
        string $migration = ''
    ): array {
        return [
            $f = [...$this->names($names[0] ?: 'post'), $keys[0], []],
            $t = [...$this->names($names[1] ?: 'comment'), $keys[1], []],
            $m = [...$this->names($this->mediatorName($names)), $keys[2], [], $migration],
            $this->setModels([$f[2], $t[2], $m[2]], $packages),
        ];
    }

    private function mediatorName($names)
    {
        return $names[2] ?: ($names[1] ?: 'comment') . 'able';
    }

    private function deleteMediator($models)
    {
        return [...$models, 'mediator' => ['', '', '']];
    }
}
