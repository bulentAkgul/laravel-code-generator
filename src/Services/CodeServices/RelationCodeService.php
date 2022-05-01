<?php

namespace Bakgul\CodeGenerator\Services\CodeServices;

use Bakgul\CodeGenerator\CodeGenerator;
use Bakgul\CodeGenerator\Services\RequestServices\RelationRequestService;
use Bakgul\CodeGenerator\Tasks\ExtendRequestForSide;
use Bakgul\CodeGenerator\Tasks\HandlePivot;
use Bakgul\CodeGenerator\Tasks\HandlePolymorphy;
use Bakgul\CodeGenerator\Tasks\HandleThrough;
use Bakgul\CodeGenerator\Tasks\InsertRelation;
use Bakgul\CodeGenerator\Tasks\InsertCode;
use Bakgul\Kernel\Tasks\MutateStub;

class RelationCodeService extends CodeGenerator
{
    public static function create($request)
    {
        $request = RelationRequestService::handle($request);

        self::insertMethods($request);

        self::insertForeignKeys($request);

        self::handleMediator($request);
    }

    private static function insertMethods(array $request)
    {
        foreach (self::sides($request) as $side) {
            $request = ExtendRequestForSide::method($request, $side);

            InsertRelation::_($request, MutateStub::get($request));
        }
    }

    private static function sides(array $requesrt): array
    {
        return $requesrt['attr']['is_through'] ? ['From'] : ['From', 'To'];
    }

    private static function insertForeignKeys(array $request)
    {
        if (self::hasNotForeignKey($request['attr'])) return;

        InsertCode::key(ExtendRequestForSide::foreignKey($request, 'to'));
    }

    private static function hasNotForeignKey(array $attr): bool
    {
        return $attr['is_mtm'] || $attr['is_through'] || $attr['polymorphic'];
    }

    private static function handleMediator(array $request)
    {
        match (true) {
            $request['attr']['is_mtm'] => HandlePivot::_($request),
            $request['attr']['is_through'] => HandleThrough::_($request),
            $request['attr']['polymorphic'] => HandlePolymorphy::_($request),
            default => null
        };
    }
}
