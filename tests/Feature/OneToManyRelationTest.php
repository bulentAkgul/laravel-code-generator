<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Tests\Tasks\SetupTest;

class OneToManyRelationTest extends TestCase
{
    /** @test */
    public function one_to_many_with_default_foreign_ids_will_be_added_to_models()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('otm', array_keys($models));

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, $pair != 'Post') . '()',
                13 => 'return $this->' . ($pair == 'Post' ? 'hasMany' : 'belongsTo') . "({$pair}::class);"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function one_to_many_with_custom_foreign_ids_will_be_added_to_models()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('otm', array_keys($models), modifiers: ['for_u_id', 'for_p_is']);

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, $pair != 'Post') . '()',
                13 => 'return $this->' . ($pair == 'Post' ? 'hasMany' : 'belongsTo') . "({$pair}::class, 'for_u_id', 'for_p_is');"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }
}
