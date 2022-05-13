<?php

namespace Bakgul\CodeGenerator\Tasks;

class HandlePolymorphy
{
    public static function _(array $request, bool $earlyReturn = false)
    {
        $request['map']['lines'] = SetMigrationLine::_($request, 'mediator');

        if ($earlyReturn) return $request['map']['lines'];

        $request['attr']['target_file'] = FindMigration::_($request, self::setSide($request));

        InsertCode::key($request);
    }

    private static function setSide($request)
    {
        return $request['attr']['relation'] == 'mtm' ? 'mediator' : 'to';
    }
}
