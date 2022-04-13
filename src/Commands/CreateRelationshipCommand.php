<?php

namespace Bakgul\CodeGenerator\Commands;

use Bakgul\CodeGenerator\Services\CodeServices\RelationCodeService;
use Bakgul\Kernel\Concerns\HasPreparation;
use Bakgul\Kernel\Concerns\HasRequest;
use Bakgul\Kernel\Concerns\Sharable;
use Bakgul\Evaluator\Concerns\ShouldBeEvaluated;
use Bakgul\Evaluator\Services\RelationCommandEvaluationService;
use Illuminate\Console\Command;

class CreateRelationshipCommand extends Command
{
    use HasPreparation, HasRequest, Sharable, ShouldBeEvaluated;

    protected $signature = '
        create:relation
        {relation}
        {from : package/model:column}
        {to : package/model:column}
        {pivot? : package/table:model}
        {--p|polymorphic}
    ';

    protected $description = '
        This command will generate methods to create eloquent relationships between the specified models.
        - relation: This is the one of the keys or values in config("packagify.code.relations.types")}
        - from: The model that is "has" side of the relationship. Package***, Column**.
        - to: The model that is "belongsTo" side of the relationship. Package***, Column***.
        *** Package: All reasonable Model paths will be scanned to find the model, unless it\'s specified.
        *** Column: It will be set based on Laravel\'s naming convention, unless it\'s specified.
        - pivot: This will be used when the relationship is many-to-many.
                 If package name is specified, the migration will be created in that package.
                 Otherwise, it will be created in the same path that "from" model is located.
                 If a model name is specified, a model for pivot table will be created.
                 If the model name is "y" or "true", it will be named based on the pivot table.
                 Otherwise, there won\'t be any model for pivot table.
                 If table name is not specified, it\'s name will be generated based on Laravel\'s naming convention.
    ';

    public $command;

    public function __construct()
    {
        $this->setEvaluator(RelationCommandEvaluationService::class);

        parent::__construct();
    }

    public function handle()
    {
        $this->prepareRequest();

        $this->evaluate();

        if ($this->stop()) return $this->terminate();

        RelationCodeService::create($this->request);
    }
}
