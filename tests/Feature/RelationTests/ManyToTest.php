<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\ManyToAssertion;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Tasks\ConvertCase;

class ManyToTest extends TestCase
{
    use ManyToAssertion;

    /** @test */
    public function mm_without_pivot_without_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare();

        $this->create("{$from[0]} {$to[0]}");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mm_without_pivot_with_default_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare();

        $this->create("{$from[0]} {$to[0]} -m");

        $this->assertCase($from, $to, $mediator, $models);
    }

    /** @test */
    public function mm_with_custom_pivot_without_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(migration: 'image-file');

        $this->create("{$from[0]} {$to[0]} image-file");

        $this->assertCase($from, $to, $mediator, $this->deleteMediator($models));
    }

    /** @test */
    public function mm_without_pivot_with_custom_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(['', '', 'image-files']);

        $this->create("{$from[0]} {$to[0]} image-files -m");

        $this->assertCase($from, $to, $mediator, $models);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_without_keys_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(['', '', 'image-files'], migration: 'images');

        $this->create("{$from[0]} {$to[0]} images:image-files");

        $this->assertCase($from, $to, $mediator, $models);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_with_from_key_without_to_key_without_package()
    {
        $this->init('mtm');

        [$from, $to, $mediator, $models] = $this->prepare(['', '', 'image-files'], keys: ['custom_id', '', ''], migration: 'images');

        $this->create("{$from[0]}:custom_id {$to[0]} images:image-files");

        $this->assertCase($from, $to, $mediator, $models);
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
            $m = [...$this->names($names[2] ?: $this->mediatorName($names)), $keys[2], [], $migration],
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
