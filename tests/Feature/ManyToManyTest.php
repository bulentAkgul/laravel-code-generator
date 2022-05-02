<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Helpers\Path;
use Bakgul\Kernel\Tests\Tasks\SetupTest;

class ManyToManyTest extends TestCase
{
    /** @test */
    public function mm_without_package_without_keys_without_pivot()
    {
        //
    }
    /** @test */
    public function many_to_many_with_defaults_will_be_added_to_models_and_create_pivot_migration()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('mtm', array_keys($models));

        $pivot = $this->defaultPivot($models);

        $this->assertFileExists(Path::glue([$this->database('migrations'), $this->migration($pivot)]));

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, false) . '()',
                13 => "return \$this->belongsToMany({$pair}::class, '{$pivot}');"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function many_to_many_with_default_pivot_and_custom_foreign_ids_will_be_added_to_models_and_create_pivot_migration()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('mtm', array_keys($models), modifiers: ['for_u_id', 'for_p_id']);

        $pivot = $this->defaultPivot($models);

        $this->assertFileExists($this->database('migrations') . "/{$this->migration($pivot)}");

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, false) . '()',
                13 => "return \$this->belongsToMany({$pair}::class, '{$pivot}', 'for_u_id', 'for_p_id');"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function many_to_many_with_custom_pivot_and_default_foreign_ids_will_be_added_to_models_and_create_pivot_migration()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();
        $pivot = 'custom-pivot';
        $class = Convention::class($pivot);

        $this->callCommand('mtm', array_keys($models), $pivot);

        $this->assertFileExists($this->database('migrations') . "/{$this->migration(ConvertCase::snake($pivot))}");
        $this->assertFileExists($this->database('seeders') . "/{$class}Seeder.php");
        $this->assertFileExists($this->database('factories') . "/{$class}Factory.php");

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, false) . '()',
                13 => "return \$this->belongsToMany({$pair}::class)->using({$class}::class);"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function many_to_many_with_custom_pivot_and_foreign_ids_will_be_added_to_models_and_create_pivot_migration()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();
        $pivot = 'custom-pivot';
        $class = Convention::class($pivot);

        $this->callCommand('mtm', array_keys($models), $pivot, ['for_u_id', 'for_p_id']);

        $pivot = ConvertCase::snake($pivot);

        $this->assertFileExists($this->database('migrations') . "/{$this->migration($pivot)}");
        $this->assertFileExists($this->database('seeders') . "/{$class}Seeder.php");
        $this->assertFileExists($this->database('factories') . "/{$class}Factory.php");

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, false) . '()',
                13 => "return \$this->belongsToMany({$pair}::class, '{$pivot}', 'for_u_id', 'for_p_id')->using({$class}::class);"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function many_to_many_with_custom_pivot_and_different_pivot_class_and_custom_foreign_ids_will_be_added_to_models_and_create_pivot_migration()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();
        $table = 'pivot-table';
        $model = 'pivot-model';

        $this->callCommand('mtm', array_keys($models), $model, ['for_u_id', 'for_p_id', $table]);

        $model = Convention::class($model);
        $table = ConvertCase::snake($table);

        $this->assertFileExists($this->database('migrations') . "/{$this->migration($table)}");
        $this->assertFileExists($this->database('seeders') . "/{$model}Seeder.php");
        $this->assertFileExists($this->database('factories') . "/{$model}Factory.php");

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, false) . '()',
                13 => "return \$this->belongsToMany({$pair}::class, '{$table}', 'for_u_id', 'for_p_id')->using({$model}::class);"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function many_to_many_polymorphic()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();
        $pivot = 'images';
        $model = 'image';

        $this->callCommand(
            'mtm',
            array_keys($models),
            $pivot,
            ['', '', $model],
            ['polymorphic' => true]
        );
    }
}
