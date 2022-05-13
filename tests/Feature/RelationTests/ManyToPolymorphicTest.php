<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\ManyToPolymorphicAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;

class ManyToPolymorphicTest extends TestCase
{
    use TestExecutions, TestPreparations, ManyToPolymorphicAssertion;

    /**
     * Pivot table, its columns, and mediator model
     * will always be generated in the default names.
     */

     /** @test */
    public function mp_without_pivot_without_model_without_keys()
    {
        $this->runManyToTest(isPoly: true, isMediatorless: true);
    }

    /** @test */
    public function mp_with_pivot_without_model_without_keys()
    {
        $this->runManyToTest(['', '', 'image_file'], isPoly: true);
    }

    /** @test */
    public function mp_without_pivot_without_model_with_keys()
    {
        $this->runManyToTest(['', '', ''], keys: ['from', 'to', ''], isPoly: true);
    }

    private function mediatorName($names)
    {
        return ($names[1] ?: 'comment') . 'able';
    }
}
