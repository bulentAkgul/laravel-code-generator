<?php

namespace Bakgul\CodeGenerator\Tests\Feature\RelationTests;

use Bakgul\CodeGenerator\Tests\Assertions\HasThroughAssertion;
use Bakgul\CodeGenerator\Tests\Concerns\TestExecutions;
use Bakgul\CodeGenerator\Tests\Concerns\TestPreparations;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;

class HasThroughPackageVariationsTest extends TestCase
{
    use TestExecutions, TestPreparations, HasThroughAssertion;

    /** @test */
    public function ht_without_packages()
    {
        $this->runHasThrough(packages: ['', '', '']);
    }

    /** @test */
    public function ht_with_from_package_without_to_package_without_mediator_package()
    {
        $this->runHasThrough(packages: ['testing', '', '']);
    }

    /** @test */
    public function ht_with_from_package_with_to_package_without_mediator_package()
    {
        $this->runHasThrough(packages: ['testing', 'testing', '']);
    }

    /** @test */
    public function ht_with_from_package_with_different_to_package_without_mediator_package()
    {
        $this->runHasThrough(['', 'users', ''], ['testing', 'users', ''], uses: ['use Core\Users\Models\User;']);
    }

    /** @test */
    public function ht_without_from_package_with_to_package_without_mediator_package()
    {
        $this->runHasThrough(packages: ['', 'testing', '']);
    }

    /** @test */
    public function ht_with_from_package_without_to_package_with_mediator_package()
    {
        $this->runHasThrough(packages: ['testing', '', 'testing']);
    }

    /** @test */
    public function ht_with_from_package_with_to_package_with_mediator_package()
    {
        $this->runHasThrough(packages: ['testing', 'testing', 'testing']);
    }

    /** @test */
    public function ht_with_from_package_with_different_to_package_with_different_mediator_package()
    {
        $this->runHasThrough(
            ['', 'users', ''],
            ['testing', 'users', 'new-users'],
            uses: ['use Core\NewUsers\Models\Image;', 'use Core\Users\Models\User;'],
            makeDir: true
        );
    }

    /** @test */
    public function ht_without_from_package_with_to_package_with_mediator_package()
    {
        $this->runHasThrough(packages: ['', 'testing', 'testing']);
    }
}
