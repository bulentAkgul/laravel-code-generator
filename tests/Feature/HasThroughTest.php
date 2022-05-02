<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\Assertions\HasThroughAssertion;
use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Helpers\Settings;

class HasThroughTest extends TestCase
{
    use HasThroughAssertion;

    /** @test */
    public function ht_without_packages_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare();

                $this->create("{$from[0]} {$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_without_to_package_without_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(packages: [$this->testPackage['name'], '', '']);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_with_to_package_without_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(packages: [$this->testPackage['name'], $this->testPackage['name'], '']);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$this->testPackage['name']}/{$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_with_different_to_package_without_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(['', 'user', ''], [$this->testPackage['name'], 'users', '']);

                if ($isAlone == 'pl') {
                    $from[4] = ['use Core\Users\Models\User;'];
                }

                $this->create("{$this->testPackage['name']}/{$from[0]} users/{$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_without_from_package_with_to_package_without_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(['', '', ''], ['', $this->testPackage['name'], '']);

                $this->create("{$from[0]} {$this->testPackage['name']}/{$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_without_to_package_with_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(packages: [$this->testPackage['name'], '', $this->testPackage['name']]);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$to[0]} {$this->testPackage['name']}/{$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_with_to_package_with_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(packages: [$this->testPackage['name'], $this->testPackage['name'], $this->testPackage['name']]);

                $this->create("{$this->testPackage['name']}/{$from[0]} {$this->testPackage['name']}/{$to[0]} {$this->testPackage['name']}/{$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_with_from_package_with_different_to_package_with_different_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                if ($isAlone == 'pl') mkdir(Path::glue([base_path(), Settings::main('packages_root'), 'core', 'new-users']));

                [$from, $to, $mediator, $models] = $this->prepare(['', 'user', ''], [$this->testPackage['name'], 'users', 'new-users']);

                if ($isAlone == 'pl') {
                    $from[4] = ['use Core\NewUsers\Models\Image;', 'use Core\Users\Models\User;'];
                }

                $this->create("{$this->testPackage['name']}/{$from[0]} users/{$to[0]} new-users/{$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_without_from_package_with_to_package_with_mediator_package_without_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(['', '', ''], ['', $this->testPackage['name'], $this->testPackage['name']]);

                $this->create("{$from[0]} {$this->testPackage['name']}/{$to[0]} {$this->testPackage['name']}/{$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_without_package_with_from_key_without_to_key()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(keys: ['from_id', '', '']);

                $this->create("{$from[0]}:from_id {$to[0]} {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_without_package_without_from_key_with_to_key()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(keys: ['', 'to_id', '']);

                $this->create("{$from[0]} {$to[0]}:to_id {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    /** @test */
    public function ht_without_package_with_keys()
    {
        foreach (['oto', 'otm'] as $mode) {
            $this->mode = $mode;

            foreach (['sl', 'sp', 'pl'] as $isAlone) {
                $this->setupTest($isAlone);

                [$from, $to, $mediator, $models] = $this->prepare(keys: ['from_id', 'to_id', '']);

                $this->create("{$from[0]}:from_id {$to[0]}:to_id {$mediator[0]}");

                $this->assertCase($from, $to, $mediator, $models);
            }
        }
    }

    private function prepare(array $names = ['', '', ''], array $packages = ['', '', ''], array $keys = ['', '', '']): array
    {
        return [
            $f = [...$this->names($names[0] ?: 'post'), $keys[0], []],
            $t = [...$this->names($names[1] ?: 'comment'), $keys[1], []],
            $m = [...$this->names($names[2] ?: 'image'), $keys[2], []],
            $this->setModels([$f[2], $t[2], $m[2]], $packages),
        ];
    }
}
