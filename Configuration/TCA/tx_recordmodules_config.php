<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'Record Module Configuration',
        'label' => 'tablename',
        'label_alt' => 'title',
        'label_alt_force' => true,
        'default_sortby' => 'ORDER BY sorting ASC',
        'sortby' => 'sorting',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden'
        ],
        'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/content/content-card.svg',
        'rootLevel' => true,
        'hideTable' => false
    ],
    'types' => [
        '1' => [
            'showitem' => '
            hidden,tablename,parent,pids,root_level,title,iconIdentifier,icon'
        ]
    ],
    'columns' => [
        'hidden' => $GLOBALS['TCA']['tt_content']['columns']['hidden'],
        'tablename' => [
            'label' => 'Tabelle',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Rfuehricht\Recordmodules\Backend\ItemsProcFunc::class . '->getAllTables',
                'eval' => 'required',
                'sortItems' => [
                    'label' => 'asc'
                ]
            ]
        ],
        'parent' => [
            'label' => 'Position',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => \Rfuehricht\Recordmodules\Backend\ItemsProcFunc::class . '->getAllToplevelModules',
                'eval' => 'required',
                'default' => 'recordmodules',
                'sortItems' => [
                    'label' => 'asc'
                ]
            ]
        ],
        'pids' => [
            'label' => 'Seiten',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'size' => 10,
                'suggestOptions' => [
                    'default' => [
                        'searchWholePhrase' => 1,
                        'searchCondition' => 'sys_language_uid = 0',
                        'additionalSearchFields' => 'title,nav_title'
                    ]
                ]
            ]
        ],
        'root_level' => [
            'label' => 'List Records from root level',
            'description' => 'If set, "pids" will not be taken into account. Backend module will only list records from root level without showing page tree.',
            'config' => [
                'type' => 'check',
            ]
        ],
        'title' => [
            'label' => 'Title',
            'description' => 'Custom title for the module. Default: Title of the table as defined in TCA',
            'config' => [
                'type' => 'input'
            ]
        ],
        'iconIdentifier' => [
            'label' => 'Icon Identifier',
            'description' => 'Custom icon identifier for the module. Default: Icon of the table as defined in TCA',
            'config' => [
                'type' => 'input'
            ]
        ],
        'icon' => [
            'label' => 'Icon',
            'description' => 'Custom icon for the module. Overrides iconIdentifier if set. Default: Icon of the table as defined in TCA',
            'config' => [
                'type' => 'file',
                'max' => 1,
                'allowed' => 'common-image-types'
            ]
        ]
    ]
];
