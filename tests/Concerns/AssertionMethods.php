<?php

namespace Bakgul\CodeGenerator\Tests\Concerns;

use Bakgul\Kernel\Helpers\Text;

trait AssertionMethods
{
    public function makeLocalKey(string $key): string
    {
        return $key == 'id' ? '' : '$table->integer' . $this->inject($key) . ';';
    }
    
    public function close()
    {
        return '});';
    }

    public function inject($value) {
        return Text::inject($value, ['(', 'sq']);
    }
}