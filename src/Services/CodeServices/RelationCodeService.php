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
    private static $request;

    public static function create($request)
    {
        self::makeRequest($request);
ray(self::$request);
        self::insertModelCodes();

        self::insertMigrationLines();

        self::handleMediator();
    }

    private static function makeRequest($request)
    {
        self::$request = RelationRequestService::handle($request);
    }

    private static function insertModelCodes()
    {
        foreach (['From', 'To'] as $side) {
            if (self::hasModelNoCode($side)) continue;

            $request = ExtendRequestForSide::model(self::$request, $side);

            InsertRelation::_($request, MutateStub::get($request));

            InsertCode::uses($request);
        }
    }

    private static function insertMigrationLines()
    {
        foreach (['from', 'to'] as $side) {
            $request = ExtendRequestForSide::migration(self::$request, $side);

            if (array_filter($request['map']['lines'])) InsertCode::key($request);
        }
    }

    private static function handleMediator()
    {
        match (true) {
            self::$request['attr']['is_mtm'] => HandlePivot::_(self::$request),
            self::$request['attr']['is_through'] => HandleThrough::_(self::$request),
            self::$request['attr']['polymorphic'] => HandlePolymorphy::_(self::$request),
            default => null
        };
    }

    private static function hasModelNoCode($side)
    {
        return in_array($side, ['to', 'To']) && self::$request['attr']['is_through'];
    }
}
