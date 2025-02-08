<?php

declare(strict_types=1);

$hasActiveModules = false;

$modules = [];
foreach ($GLOBALS['TCA'] as $table => $settings) {
    if (isset($settings['ctrl']['recordModule']['activate']) &&
        boolval($settings['ctrl']['recordModule']['activate']) === true) {

        $typeIcons = $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'] ?? [];
        $modules['recordmodules_module_' . $table] = [
            'parent' => 'recordmodules',
            'access' => 'user',
            'workspaces' => 'live',
            'path' => '/module/record/' . $table,
            'labels' => [
                'title' => $GLOBALS['TCA'][$table]['ctrl']['title']
            ],
            'iconIdentifier' => $GLOBALS['TCA'][$table]['ctrl']['iconIdentifier'] ?? array_shift($typeIcons),
            'icon' => $GLOBALS['TCA'][$table]['ctrl']['icon'] ?? $GLOBALS['TCA'][$table]['ctrl']['iconfile'] ?? '',
            'extensionName' => 'Recordmodules',
            'navigationComponent' => !isset($GLOBALS['TCA'][$table]['ctrl']['recordModule']['pids']) ? '@typo3/backend/page-tree/page-tree-element' : '',

            'routes' => [
                '_default' => [
                    'target' => \Rfuehricht\Recordmodules\Controller\ModuleController::class . '::mainAction',
                ],
            ],
            'moduleData' => [
                'table' => $table,
                'clipBoard' => true,
                'searchBox' => true
            ],
        ];
        $hasActiveModules = true;
    }
}

if ($hasActiveModules) {
    $modules['recordmodules'] = [
        'labels' => 'LLL:EXT:recordmodules/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'actions-brand-typo3',
        'extensionName' => 'Recordmodules',
        'position' => ['after' => 'web'],
        'navigationComponent' => ''
    ];
}

return $modules;