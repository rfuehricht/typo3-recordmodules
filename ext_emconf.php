<?php

$EM_CONF['recordmodules'] = [
    'title' => 'Modules for records',
    'description' => 'Adds backend modules for configured record types.',
    'category' => 'backend',
    'version' => '1.0.5',
    'state' => 'stable',
    'author' => 'Reinhard Führicht',
    'author_email' => 'r.fuehricht@gmail.com',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-13.99.99'
        ],
        'conflicts' => [
        ],
    ],
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1
];
