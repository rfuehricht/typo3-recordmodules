<?php

$EM_CONF['recordmodules'] = [
    'title' => 'Modules for records',
    'description' => 'Adds backend modules for configured record types.',
    'category' => 'backend',
    'version' => '1.3.1',
    'state' => 'stable',
    'author' => 'Reinhard Führicht',
    'author_email' => 'r.fuehricht@gmail.com',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-14.99.99'
        ],
        'conflicts' => [
        ],
    ],
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1
];
