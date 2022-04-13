<?php

return [
    'relations' => [
        'types' => [
            'oto' => 'one_to_one',
            'otm' => 'one_to_many',
            'mtm' => 'many_to_many',
        ],
        'variations' => [
            'p' => 'polymorphic',
        ]
    ]
];
