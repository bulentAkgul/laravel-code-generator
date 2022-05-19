<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\ManyToAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Arry;
use Bakgul\Kernel\Tasks\ConvertCase;

class ManyToDefaultTest extends TestCase
{
    use TestExecutions, TestPreparations, ManyToAssertion;

    /** @test */
    public function mm_without_pivot_without_model_without_keys()
    {
        $this->runManyToTest();
    }

    /** @test */
    public function mm_without_pivot_with_default_model_without_keys()
    {
        $this->runManyToTest(isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_custom_pivot_without_model_without_keys()
    {
        $this->runManyToTest(['', '', 'image_file']);
    }

    /** @test */
    public function mm_without_pivot_with_custom_model_without_keys()
    {
        $this->runManyToTest(['', '', 'image_file'], isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_without_keys()
    {
        $this->runManyToTest(['', '', 'image_file'], models: ['', '', 'image'], isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_with_from_key_without_to_key()
    {
        $this->runManyToTest(['', '', 'image_file'], keys: ['from', '', ''], models: ['', '', 'image'], isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_with_keys()
    {
        $this->runManyToTest(['', '', 'image_file'], keys: ['from', 'to', ''], models: ['', '', 'image'], isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_with_full_keys()
    {
        $this->runManyToTest(['', '', 'image_file'], keys: ['from_id', 'to_id', ''], models: ['', '', 'image'], isMediatorless: false, hasModel: true);
    }

    /** @test */
    public function mm_with_pivot_with_different_model_without_from_key_with_to_key()
    {
        $this->runManyToTest(['', '', 'image_file'], keys: ['', 'to', ''], models: ['', '', 'image'], isMediatorless: false, hasModel: true);
    }

    private function mediatorName($names)
    {
        return  implode('-', array_map(
            fn ($x) => ConvertCase::kebab($x, true),
            Arry::sort([$names[0] ?: 'post', $names[1] ?: 'comment'])
        ));
    }
}
