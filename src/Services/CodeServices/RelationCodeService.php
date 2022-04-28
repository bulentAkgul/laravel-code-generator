<?php

namespace Bakgul\CodeGenerator\Services\CodeServices;

use Bakgul\CodeGenerator\CodeGenerator;
use Bakgul\CodeGenerator\Services\RequestServices\CodeRequestServices\RelationCodeRequestService;
use Bakgul\CodeGenerator\Tasks\HandlePivot;
use Bakgul\CodeGenerator\Tasks\InsertRelation;
use Bakgul\Kernel\Tasks\MutateStub;

class RelationCodeService extends CodeGenerator
{
    public static function create($request)
    {
        $request = RelationCodeRequestService::handle($request);

        self::insert($request);

        HandlePivot::_($request);
    }

    private static function insert(array $request)
    {
        array_map(fn ($x) => self::insertCode($request, $x), ['From', 'To']);
    }

    private static function insertCode(array $request, string $modelKey)
    {
        $request = RelationCodeRequestService::modelCode($request, $modelKey);
        
        InsertRelation::_($request, MutateStub::get($request));
    }
}
