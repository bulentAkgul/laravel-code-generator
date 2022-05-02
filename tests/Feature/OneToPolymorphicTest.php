<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\Assertions\OneToPolymorphicAssertion;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Settings;

class OneToPolymorphicTest extends TestCase
{
    use OneToPolymorphicAssertion;

    /** @test */
    public function op_without_packages_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare();

                $this->create("{$from[0]} {$to[0]} -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_with_from_package_without_to_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(packages: [$this->testPackage['name'], '', '']);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$to[0]} -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_with_from_package_with_to_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(packages: [$this->testPackage['name'], $this->testPackage['name'], '']);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$this->testPackage['name']}/{$to[0]} -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_with_from_package_with_different_to_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(['', 'user', ''], [$this->testPackage['name'], 'users', '']);

                if ($isAlone == 'pl') {
                    $from[4] = ['use Core\Users\Models\User;'];
                }
                
                $this->create("{$this->testPackage['name']}/{$from[0]} users/{$to[0]} -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_without_package_with_from_key_without_to_key()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(keys: ['from_id', '', '']);

                $this->create("{$from[0]}:from_id {$to[0]} -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_without_package_without_from_key_with_to_key()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(keys: ['', 'to_id', '']);

                $this->create("{$from[0]} {$to[0]}:to_id -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_without_package_with_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare(keys: ['from_id', 'to_id', '']);

                $this->create("{$from[0]}:from_id {$to[0]}:to_id -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    /** @test */
    public function op_with_ignored_mediator()
    {
        Settings::set('evaluator.evaluate_commands', false);

        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $models] = $this->prepare();

                $this->create("{$from[0]} {$to[0]} 'mediator' -p");

                $this->assertCase($from, $to, $models);
            }
        }
    }

    private function prepare(array $names = ['', '', ''], array $packages = ['', '', ''], array $keys = ['', '', '']): array
    {
        return [
            $f = [...$this->names($names[0] ?: 'post'), $keys[0], []],
            $t = [...$this->names($names[1] ?: 'comment'), $keys[1], []],
            $this->setModels([$f[2], $t[2], ''], $packages),
        ];
    }
}
