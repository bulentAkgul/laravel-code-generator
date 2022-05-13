<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\OneToPolymorphicAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;

class OneToPolymorphicTest extends TestCase
{
    use TestExecutions, TestPreparations, OneToPolymorphicAssertion;

    /** @test */
    public function op_without_packages_without_keys()
    {
        $this->runOneTo(isPoly: true);
    }

    /** @test */
    public function op_with_from_package_without_to_package_without_keys()
    {
        $this->runOneTo(packages: ['testing', '', ''], isPoly: true);
    }

    /** @test */
    public function op_with_from_package_with_to_package_without_keys()
    {
        $this->runOneTo(packages: ['testing', 'testing', ''], isPoly: true);
    }

    /** @test */
    public function op_with_from_package_with_different_to_package_without_keys()
    {
        $this->runOneTo(['', 'vip-users', ''], ['testing', 'users', ''], uses: [
            'from' => ['use Core\Users\Models\VipUser;']
        ], isPoly: true);
    }

    /** @test */
    public function op_without_package_with_from_key_without_to_key()
    {
        $this->runOneTo(keys: ['from', '', ''], isPoly: true);
    }

    /** @test */
    public function op_without_package_without_from_key_with_to_key()
    {
        $this->runOneTo(keys: ['', 'to', ''], isPoly: true);
    }

    /** @test */
    public function op_without_package_with_keys()
    {
        $this->runOneTo(keys: ['from', 'to', ''], isPoly: true);
    }
}
