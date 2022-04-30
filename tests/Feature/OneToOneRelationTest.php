<?php

namespace Bakgul\CodeGenerator\Tests\Feature;

use Bakgul\CodeGenerator\Tests\TestCase;
use Bakgul\Kernel\Tasks\ConvertCase;
use Bakgul\Kernel\Helpers\Convention;
use Bakgul\Kernel\Tests\Tasks\SetupTest;

class OneToOneRelationTest extends TestCase
{
    /** @test */
    public function temp_test()
    {
        ray()->clearAll();
        $this->testPackage = (new SetupTest)();

        $this->setModels();

        $this->artisan([
            0 => 'create:relation oto post comment',
            1 => 'create:relation otm post comment',
            2 => 'create:relation oto post comment -p',  // polymorphic
            3 => 'create:relation otm post comment -p',  // polymorphic
            4 => 'create:relation oto post comment image',  // through
            5 => 'create:relation otm post comment image',  // through
            6 => 'create:relation oto post comment image -p',  // polymorphic (ignore through)
            7 => 'create:relation otm post comment image -p',  // polymorphic (ignore through)
            8 => 'create:relation mtm post comment', // pivot
            9 => 'create:relation mtm post comment -p', // pivot + polymorphic
            10 => 'create:relation mtm post comment images', // pivot
            11 => 'create:relation mtm post comment images:y', // pivot
            12 => 'create:relation mtm post comment images -p', // pivot + polymorphic
        ][12]);

        return;
        

        $commands = [];
        foreach (['oto', 'otm', 'mtm'] as $type) {
            foreach (['', 'image'] as $middleman) {
                foreach ([false, true] as $p) {
                    $command = implode(' ', array_filter([
                        'create:relation',
                        $type,
                        "{$this->testPackage['name']}/user",
                        'post',
                        $middleman,
                        $p ? '-p' : ''
                    ]));
                    $commands[] = $command;
                    // $this->artisan($command);
                }
            }
        }
    }
    /** @test */
    public function one_to_one_with_default_foreign_ids_will_be_added_to_models()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('oto', array_keys($models));

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, true) . '()',
                13 => 'return $this->' . ($pair == 'Post' ? 'hasOne' : 'belongsTo') . "({$pair}::class);"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }

    /** @test */
    public function one_to_one_with_custom_foreign_ids_will_be_added_to_models()
    {
        $this->testPackage = (new SetupTest)();

        $models = $this->setModels();

        $this->callCommand('oto', array_keys($models), modifiers: ['for_u_id', 'for_p_is']);

        foreach ($models as $name => $path) {
            $pair = $this->getPair($models, $name);

            $expectation = [
                7 => "class " . Convention::class($name) . " extends Model",
                11 => 'public function ' . ConvertCase::camel($pair, true) . '()',
                13 => 'return $this->' . ($pair == 'Post' ? 'hasOne' : 'belongsTo') . "({$pair}::class, 'for_u_id', 'for_p_is');"
            ];

            $this->assertFileExists($path);

            $content = file($path);

            foreach ($expectation as $i => $line) {
                $this->assertEquals($line, trim($content[$i]));
            }
        }
    }
}
