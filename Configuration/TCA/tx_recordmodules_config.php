<?php

use Rfuehricht\Recordmodules\Backend\ItemsProcFunc;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$languagePrefix = 'LLL:EXT:recordmodules/Resources/Private/Language/locallang_tca.xlf:tx_recordmodules_config.';

return [
    'ctrl' => [
        'title' => $languagePrefix . 'tcaTitle',
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
        'tablename' => [
            'label' => $languagePrefix . 'tablename',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => ItemsProcFunc::class . '->getAllTables',
                'eval' => 'required',
                'sortItems' => [
                    'label' => 'asc'
                ]
            ]
        ],
        'parent' => [
            'label' => $languagePrefix . 'parent',
            'description' => $languagePrefix . 'parent.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => ItemsProcFunc::class . '->getAllToplevelModules',
                'eval' => 'required',
                'default' => 'recordmodules',
                'sortItems' => [
                    'label' => 'asc'
                ]
            ]
        ],
        'pids' => [
            'label' => $languagePrefix . 'pids',
            'description' => $languagePrefix . 'pids.description',
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
            'label' => $languagePrefix . 'root_level',
            'description' => $languagePrefix . 'root_level.description',
            'config' => [
                'type' => 'check',
            ]
        ],
        'title' => [
            'label' => $languagePrefix . 'title',
            'description' => $languagePrefix . 'title.description',
            'config' => [
                'type' => 'input'
            ]
        ],
        'iconIdentifier' => [
            'label' => $languagePrefix . 'iconIdentifier',
            'description' => $languagePrefix . 'iconIdentifier.description',
            'config' => [
                'type' => 'input'
            ]
        ],
        'icon' => [
            'label' => $languagePrefix . 'icon',
            'description' => $languagePrefix . 'icon.description',
            'config' => [
                'type' => 'file',
                'max' => 1,
                'allowed' => 'common-image-types'
            ]
        ]
    ]
];
