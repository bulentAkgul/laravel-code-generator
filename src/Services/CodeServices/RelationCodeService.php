<?php

namespace Bakgul\CodeGenerator\Services\CodeServices;

use Bakgul\CodeGenerator\CodeGenerator;
use Bakgul\CodeGenerator\Services\RequestServices\CodeRequestServices\RelationCodeRequestService;
use Bakgul\Kernel\Tasks\MutateStub;

class RelationCodeService extends CodeGenerator
{
    public static function create($request)
    {
        $request = RelationCodeRequestService::handle($request);

        foreach (['From', 'To'] as $modelKey) {
            self::insertCode($request, $modelKey);
        }

        self::handlePivot($request);
    }

    private static function insertCode(array $request, $modelKey)
    {
        parent::insert($r = RelationCodeRequestService::modelCode($request, $modelKey), MutateStub::get($r));
    }

    private static function handlePivot(array $request)
    {
        self::makeMigration($request);
        self::makeModel($request);
    }

    private static function makeMigration($request)
    {
        // $fileRequest = RelationFileRequestService::create($request, 'migration');

        // CompleteFolders::_($fileRequest['attr']['path']);

        // MakeFile::_($fileRequest);
    }

    private static function makeModel($request)
    {
        // if (!$request['attr']['pivot_model']) return;
        
        // $fileRequest = RelationFileRequestService::create($request, 'model');

        // MakeFile::_($fileRequest);
    }
}
