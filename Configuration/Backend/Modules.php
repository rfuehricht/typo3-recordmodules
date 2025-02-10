<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Database\ConnectionPool;

$modules = [];

$qb = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('tx_recordmodules_config');
$configurationRecords = $qb->select('*')
    ->from('tx_recordmodules_config')
    ->executeQuery()->fetchAllAssociative();

$configurationRecordsIndexed = [];
foreach ($configurationRecords as $record) {
    $configurationRecordsIndexed[$record['tablename']] = $record;
}
$createCustomModuleGroup = false;
foreach ($GLOBALS['TCA'] as $table => $settings) {

    $localSettings = $settings['ctrl']['recordModule'] ?? [];
    if (array_key_exists($table, $configurationRecordsIndexed)) {
        $localSettings = $configurationRecordsIndexed[$table];
        $localSettings['activate'] = true;
    }

    if ((isset($localSettings['activate']) &&
        boolval($localSettings['activate']) === true)) {

        $typeIcons = $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'] ?? [];

        $parent = (isset($localSettings['parent']) && trim($localSettings['parent']) !== '' ? trim($localSettings['parent']) : 'recordmodules');

        if ($parent === 'recordmodules') {
            $createCustomModuleGroup = true;
        }

        $sorting = $localSettings['sorting'] ?? 9999;

        $title = $GLOBALS['TCA'][$table]['ctrl']['title'];
        if (isset($localSettings['title']) && strlen(trim($localSettings['title'])) > 0) {
            $title = trim($localSettings['title']);
        }

        if (isset($localSettings['root_level']) && intval($localSettings['root_level']) === 1) {
            $localSettings['pids'] = '0';
        }

        $localModuleConfiguration = [
            'parent' => $parent,
            'position' => ['before' => 'web_list'],
            'sorting' => intval($sorting),
            'access' => 'user',
            'workspaces' => 'live',
            'path' => '/module/record/' . $table,
            'labels' => [
                'title' => $title
            ],
            'extensionName' => 'Recordmodules',
            'navigationComponent' => !isset($localSettings['pids']) ? '@typo3/backend/page-tree/page-tree-element' : '',
            'inheritNavigationComponentFromMainModule' => false,
            'routes' => [
                '_default' => [
                    'target' => \Rfuehricht\Recordmodules\Controller\ModuleController::class . '::mainAction',
                ],
            ],
            'moduleData' => [
                'table' => $table,
                'pids' => $localSettings['pids'] ?? [],
                'clipBoard' => true,
                'searchBox' => true
            ],
        ];

        if (isset($localSettings['icon']) && intval($localSettings['icon']) > 0) {
            $fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
            $files = $fileRepository->findByRelation(
                tableName: 'tx_recordmodules_config',
                fieldName: 'icon',
                uid: $localSettings['uid']
            );
            if ($files) {
                /** @var \TYPO3\CMS\Core\Resource\FileReference $iconFile */
                $iconFile = reset($files);
                $localModuleConfiguration['icon'] = $iconFile->getPublicUrl();
            }

        } elseif (isset($localSettings['icon']) && strlen((string)$localSettings['icon']) > 0
            && (string)$localSettings['icon'] !== '0') {
            $localModuleConfiguration['icon'] = $localSettings['icon'];
        } elseif (isset($localSettings['iconIdentifier']) && strlen($localSettings['iconIdentifier']) > 0) {
            $localModuleConfiguration['iconIdentifier'] = $localSettings['iconIdentifier'];
        } elseif (isset($GLOBALS['TCA'][$table]['ctrl']['iconfile'])) {
            $localModuleConfiguration['icon'] = $GLOBALS['TCA'][$table]['ctrl']['iconfile'];
        } elseif (count($typeIcons) > 0) {
            if (isset($typeIcons['default'])) {
                $localModuleConfiguration['iconIdentifier'] = $typeIcons['default'];
            } else {
                $localModuleConfiguration['iconIdentifier'] = reset($typeIcons);
            }
        }


        $modules['recordmodules_module_' . $table] = $localModuleConfiguration;
    }
}


if ($modules) {

    uasort($modules, function ($a, $b) {
        return $a['sorting'] <=> $b['sorting'];
    });

    if ($createCustomModuleGroup) {
        $modules['recordmodules'] = [
            'labels' => 'LLL:EXT:recordmodules/Resources/Private/Language/locallang_mod.xlf',
            'iconIdentifier' => 'actions-brand-typo3',
            'extensionName' => 'Recordmodules',
            'position' => ['after' => 'web'],
            'navigationComponent' => ''
        ];
    }
}

return $modules;