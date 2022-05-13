<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\HasThroughAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;

class HasThroughKeyVariationsTest extends TestCase
{
    use TestExecutions, TestPreparations, HasThroughAssertion;

    /** @test */
    public function ht_without_keys()
    {
        $this->runHasThrough(keys: ['', '', '']);
    }

    /** @test */
    public function ht_without_from_without_to_with_mediator_from()
    {
        $this->runHasThrough(keys: ['', '', 'first']);
    }

    /** @test */
    public function ht_without_from_without_to_with_mediator_to()
    {
        $this->runHasThrough(keys: ['', '', 'id.second']);
    }

    /** @test */
    public function ht_without_from_without_to_with_mediator_both()
    {
        $this->runHasThrough(keys: ['', '', 'first.second']);
    }

    /** @test */
    public function ht_with_from_without_to_without_mediator()
    {
        $this->runHasThrough(keys: ['month', '', '']);
    }

    /** @test */
    public function ht_with_from_without_to_with_mediator_from()
    {
        $this->runHasThrough(keys: ['month', '', 'first']);
    }

    /** @test */
    public function ht_with_from_without_to_with_mediator_to()
    {
        $this->runHasThrough(keys: ['month', '', 'id.second']);
    }

    /** @test */
    public function ht_with_from_without_to_with_mediator_both()
    {
        $this->runHasThrough(keys: ['month', '', 'first.second']);
    }

    /** @test */
    public function ht_without_from_with_to_without_mediator()
    {
        $this->runHasThrough(keys: ['', 'week', '']);
    }

    /** @test */
    public function ht_without_from_with_to_with_mediator_from()
    {
        $this->runHasThrough(keys: ['', 'week', 'first']);
    }

    /** @test */
    public function ht_without_from_with_to_with_mediator_to()
    {
        $this->runHasThrough(keys: ['', 'week', 'id.second']);
    }

    /** @test */
    public function ht_without_from_with_to_with_mediator_both()
    {
        $this->runHasThrough(keys: ['', 'week', 'first.second']);
    }

    /** @test */
    public function ht_with_from_with_to_without_mediator()
    {
        $this->runHasThrough(keys: ['day', 'week', '']);
    }

    /** @test */
    public function ht_with_from_with_to_with_mediator_from()
    {
        $this->runHasThrough(keys: ['day', 'week', 'first']);
    }

    /** @test */
    public function ht_with_from_with_to_with_mediator_to()
    {
        $this->runHasThrough(keys: ['day', 'week', 'id.second']);
    }

    /** @test */
    public function ht_with_from_with_to_with_mediator_both()
    {
        $this->runHasThrough(keys: ['day', 'week', 'first.second']);
    }

    /** @test */
    public function ht_with_from_id_with_to_with_mediator_both___id_will_be_like_no_key()
    {
        $this->runHasThrough(keys: ['id', 'week', 'first.second']);
    }

    /** @test */
    public function ht_with_from_with_to_id_with_mediator_both___id_will_be_like_no_key()
    {
        $this->runHasThrough(keys: ['day', 'id', 'first.second']);
    }

    /** @test */
    public function ht_with_from_with_to_with_mediator_first_id___id_will_be_like_no_key()
    {
        $this->runHasThrough(keys: ['day', 'week', 'id.second']);
    }

    /** @test */
    public function ht_with_from_with_to_with_mediator_second_id___id_will_be_like_no_key()
    {
        $this->runHasThrough(keys: ['day', 'week', 'first.id']);
    }

    /** @test */
    public function ht_without_from_with_to_with_mediator_to____empty_key_be_like_id()
    {
        $this->runHasThrough(keys: ['', 'week', '.second']);
    }
}
