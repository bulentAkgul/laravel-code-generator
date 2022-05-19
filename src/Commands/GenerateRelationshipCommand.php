<?php

namespace Bakgul\CodeGenerator\Commands;

use Bakgul\CodeGenerator\Services\CodeServices\RelationCodeService;
use Bakgul\Kernel\Concerns\HasPreparation;
use Bakgul\Kernel\Concerns\HasRequest;
use Bakgul\Kernel\Concerns\Sharable;
use Bakgul\Evaluator\Concerns\ShouldBeEvaluated;
use Bakgul\Evaluator\Services\RelationCommandEvaluationService;
use Bakgul\FileHistory\Concerns\HasHistory;
use Bakgul\Kernel\Helpers\Settings;
use Illuminate\Console\Command;

class GenerateRelationshipCommand extends Command
{
    use HasHistory, HasPreparation, HasRequest, Sharable, ShouldBeEvaluated;

    protected $signature = '
        create:relation
        {relation : oto (one-to-one) || otm (one-to-many) || mtm (many-to-many)}
        {from : package/table:column:model}
        {to : package/table:column:model}
        {mediator? : package/table:column:model || package/table:model}
        {--m|model : true || false}
        {--p|polymorphic : true || false}
    ';

    protected $description = 'This command generates methods to create eloquent relationships between the specified models.';

    protected $arguments = [
        'relation' => [
            "Required",
            "It should be one of the shorthands of the relation types.",
            "oto => one to one, otm => one to many, mtm => many to many."
        ],
        'from' => [
            "Has side of the relationship.",
            'package' => [
                "Optional",
                "The model and migration are searched in that package when it's specified.",
                "Otherwise, all reasonable paths are checked to find the model and migration.",
                "It will be ignored when the repository is a Standalone Laravel or Package.",
            ],
            'table' => [
                "Required",
                "It should be the migration file name's part between 'create_' and '_table'."
            ],
            'column' => [
                "Optional",
                "Laravel's conventions will be followed when it's missing.",
                "Otherwise, the local key of the 'has' side will be the given column.",
                "It'll be the foreign key when the 'belongsTo' part has no column.",
                "It won't be prefixed if it ends with '_id.'"
            ],
            'model' => [
                "Optional",
                "If the model name can't be produced based on the Laravel's conventions",
                "out of the table name, it should be specified so we can find it."
            ]
        ],
        'to' => [
            "BelongsTo side of the relationship.",
            'package' => [
                "Optional",
                "The model and migration are searched in that package when it's",
                "specified. Otherwise, all reasonable paths are checked to find",
                "the model and migration. It will be ignored when the repository",
                "is a Standalone Laravel or Package.",
            ],
            'table' => [
                "Required",
                "It should be the migration name's part between 'create_' and '_table'."
            ],
            'column' => [
                "Optional",
                "Laravel's conventions will be followed when it's missing.",
                "Otherwise, it'll be the foreign key after being prefixed",
                "with the 'has' side's table name.",
                "It won't be prefixed if it ends with '_id.'"
            ],
            'model' => [
                "Optional",
                "If the model name can't be produced based on the Laravel's",
                "conventions out of the table name, it should be specified",
                "so we can find it."
            ]
        ],
        'mediator as bridge' => [
            "'BelongTo' of 'from' and 'Has' of 'to' when the relation is 'oto' or 'otm'.",
            'package' => [
                "Optional",
                "The model and migration are searched in that package when it's specified.",
                "If they aren't found, they will be created there. Model and migration will",
                "be searched on all reasonable paths when it isn't specified. If they can't",
                "be found, they will be created in the same folder as the 'from' side."
            ],
            'table' => [
                "Required",
                "It should be the migration name's part between 'create_' and '_table'."
            ],
            'column' => [
                "Optional",
                "Laravel's conventions will be followed when it's missing. The names",
                "of two columns glued up with a dot are expected, like 'col1.col2'.",
                "The first is the foreign key for the 'from' side, while the second is",
                "the local key in the mediator table for the 'to' side. It's possible",
                "to pass one column. In this case, the other one will be 'id'.",
                "For example, 'user' is 'user.id', '.post' is 'id.post'.",
                "It won't be prefixed if it ends with '_id.'"
            ],
            'model' => [
                "Optional",
                "If the model name can't be produced based on the Laravel's conventions",
                "out of the table name, it should be specified so we can find it."
            ]
        ],
        'mediator as pivot' => [
            "Optional: When it's missing in 'mtm', Laravel conventions will be applied.",
            'package' => [
                "Optional",
                "The model and migration are searched in that package when it's specified.",
                "If they aren't found, they will be created there. Model and migration will",
                "be searched on all reasonable paths when it isn't specified. If they can't",
                "be found, they will be created in the same folder as the 'from' side."
            ],
            'table' => [
                "Required",
                "It should be the migration name's part between 'create_' and '_table'."
            ],
            'model' => [
                "Optional",
                "A pivot model will be created with the given name when it's specified."
            ]
        ],
    ];

    protected $options = [
        'model' => [
            "When it's appended to the command, and the relation is 'mtm', and the",
            "pivot model name isn't specified, a pivot model will be created. Its",
            "name will be generated from the pivot table name."
        ],
        'polymorphic' => [
            "When it's appended to the command, a polymorphic version of the relation",
            "will be generated."
        ]
    ];

    protected $examples = [
        "otm posts comments | Create <info>One To Many</info> between <info>posts</info> (hasMany) and <info>comments</info> (belongsTo)",
        "otm posts comments -p | Create <info>One To Many Polymorphic</info> between <info>posts</info> (hasMany) and <info>comments</info> (belongsTo)",
        "oto mechanics owners cars | Create <info>Has One Through</info> between <info>mechanics</info> (hasOne) and <info>owners</info> (belongsTo) through <info>cars</info> (belongsTo mechanics, hasOne owner)",
        "mtm posts images | Create <info>Many To Many</info> between <info>posts</info> and <info>images</info> with pivot <info>image_post</info>",
        "mtm posts images -m | Create <info>Many To Many</info> between <info>posts</info> and <info>images</info> with pivot table <info>image_post</info> and pivot model <info>ImagePost</info>",
        "mtm posts:slug images | Create <info>Many To Many</info> between <info>posts</info> and <info>images</info> with pivot <info>image_post</info> whose foreign keys are <info>post_slug</info> and <info>image_id</info>",
        "mtm posts images media | Create <info>Many To Many</info> between <info>posts</info> and <info>images</info> with pivot <info>media</info>",
        "mtm posts images media:my-media | Create <info>Many To Many</info> between <info>posts</info> and <info>images</info> with pivot model <info>MyMedia</info> whose table <info>media</info>",
        "oto users phones:mobile | Create <info>One To One</info> between <info>users</info> (hasOne) and <info>phones</info> (belongsTo) where the foreign key <info>user_mobile</info>",
    ];

    public $command;

    public function __construct()
    {
        $this->setEvaluator(RelationCommandEvaluationService::class);

        parent::__construct();
    }

    public function handle()
    {
        $this->prepareRequest();

        if (Settings::evaluator('evaluate_commands')) {
            $this->evaluate();
            if ($this->stop()) return $this->terminate();
        }

        $this->logFile();

        RelationCodeService::create($this->request);
    }
}
