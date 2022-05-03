<?php

namespace Bakgul\CodeGenerator\Tasks;

use Bakgul\Kernel\Helpers\Text;

class HandlePolymorphy
{
    public static function _(array $request, bool $earlyReturn = false)
    {
        $request['map']['lines'] = [
            '$table->integer' . Text::inject("{$request['map']['able']}_id", ['(', 'sq']),
            '$table->string' . Text::inject("{$request['map']['able']}_type", ['(', 'sq']),
        ];

        if ($earlyReturn) return $request['map']['lines'];

        $request['attr']['target_file'] = FindMigration::_($request);

        InsertCode::key($request);
    }
}
