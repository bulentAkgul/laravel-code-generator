<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\OneToAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;

class OneToDefaultTest extends TestCase
{
    use TestExecutions, TestPreparations, OneToAssertion;

    /** @test */
    public function ot_without_packages_without_keys()
    {
        $this->runOneTo();
    }

    /** @test */
    public function ot_with_from_package_without_to_package_without_keys()
    {
        $this->runOneTo(packages: ['testing', '', '']);
    }

    /** @test */
    public function ot_with_from_package_with_to_package_without_keys()
    {
        $this->runOneTo(packages: ['testing', 'testing', '']);
    }

    /** @test */
    public function ot_with_from_package_with_different_to_package_without_keys()
    {
        $this->runOneTo(['', 'users', ''], ['testing', 'users', ''], uses: [
            'from' => ['use Core\Users\Models\User;'],
            'to' => ['use CurrentTest\Testing\Models\NicePost;']
        ]);
    }

    /** @test */
    public function ot_without_package_with_from_key_without_to_key()
    {
        $this->runOneTo(keys: ['from', '', '']);
    }

    /** @test */
    public function ot_without_package_without_from_key_with_to_key()
    {
        $this->runOneTo(keys: ['', 'to', '']);
    }

    /** @test */
    public function ot_without_package_with_from_key_with_full_to_key()
    {
        $this->runOneTo(keys: ['from', 'to_id', '']);
    }

    /** @test */
    public function ot_without_package_with_keys()
    {
        $this->runOneTo(keys: ['from', 'to', '']);
    }

    /** @test */
    public function ot_without_package_without_keys_with_model()
    {
        $this->runOneTo(['users', '', ''], ['users', '', ''], models: ['vip-user', '', ''], uses: [
            'from' => ['use CurrentTest\Testing\Models\Comment;'],
            'to' => ['use Core\Users\Models\VipUser;'],
        ]);
    }
}
