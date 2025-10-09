<?php

declare(strict_types=1);

use Rfuehricht\Recordmodules\Controller\ModuleController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;


function registerModule($table, $settings, &$modules): bool
{
    $createCustomModuleGroup = false;
    if ((isset($settings['activate']) &&
        boolval($settings['activate']) === true)) {

        $typeIcons = $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'] ?? [];

        $parent = (isset($settings['parent']) && trim($settings['parent']) !== '' ? trim($settings['parent']) : 'recordmodules');

        if ($parent === 'recordmodules') {
            $createCustomModuleGroup = true;
        }

        $sorting = $settings['sorting'] ?? 9999;

        $title = $GLOBALS['TCA'][$table]['ctrl']['title'];
        if (isset($settings['title']) && strlen(trim($settings['title'])) > 0) {
            $title = trim($settings['title']);
        }

        if (isset($settings['root_level']) && intval($settings['root_level']) === 1) {
            $settings['pids'] = ['0'];
        }
        if (!isset($settings['pids'])) {
            $settings['pids'] = [];
        }
        if (!is_array($settings['pids'])) {
            $settings['pids'] = GeneralUtility::intExplode(',', (string)$settings['pids'], true);
        }
        $navigationComponent = '';

        // Show page tree if no PIDs are set.
        if (count($settings['pids']) === 0) {
            $navigationComponent = '@typo3/backend/page-tree/page-tree-element';
        }

        $originalIdentifier = 'recordmodules_module_' . $table;
        $identifier = $originalIdentifier;
        if (isset($modules[$identifier])) {
            $identifier = $originalIdentifier . '_' . $settings['uid'];
        }

        $path = $identifier;

        $localModuleConfiguration = [
            'parent' => $parent,
            'position' => ['before' => 'web_list'],
            'sorting' => intval($sorting),
            'access' => 'user',
            'workspaces' => 'live',
            'path' => '/module/' . $path,
            'labels' => [
                'title' => $title
            ],
            'extensionName' => 'Recordmodules',
            'navigationComponent' => $navigationComponent,
            'inheritNavigationComponentFromMainModule' => false,
            'routes' => [
                '_default' => [
                    'target' => ModuleController::class . '::mainAction',
                ],
            ],
            'moduleData' => [
                'table' => $table,
                'title' => $title,
                'pids' => $settings['pids'] ?? [],
                'clipBoard' => true,
                'searchBox' => true
            ],
        ];

        if (isset($settings['icon']) && intval($settings['icon']) > 0) {
            $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
            $files = $fileRepository->findByRelation(
                tableName: 'tx_recordmodules_config',
                fieldName: 'icon',
                uid: $settings['uid']
            );
            if ($files) {
                /** @var FileReference $iconFile */
                $iconFile = reset($files);
                $localModuleConfiguration['icon'] = $iconFile->getPublicUrl();
            }

        } elseif (isset($settings['icon']) && strlen((string)$settings['icon']) > 0
            && (string)$settings['icon'] !== '0') {
            $localModuleConfiguration['icon'] = $settings['icon'];
        } elseif (isset($settings['iconIdentifier']) && strlen($settings['iconIdentifier']) > 0) {
            $localModuleConfiguration['iconIdentifier'] = $settings['iconIdentifier'];
        } elseif (isset($GLOBALS['TCA'][$table]['ctrl']['iconfile'])) {
            $localModuleConfiguration['icon'] = $GLOBALS['TCA'][$table]['ctrl']['iconfile'];
        } elseif (count($typeIcons) > 0) {
            if (isset($typeIcons['default'])) {
                $localModuleConfiguration['iconIdentifier'] = $typeIcons['default'];
            } else {
                $localModuleConfiguration['iconIdentifier'] = reset($typeIcons);
            }
        }

        $modules[$identifier] = $localModuleConfiguration;
    }
    return $createCustomModuleGroup;
}

$modules = [];

$qb = GeneralUtility::makeInstance(ConnectionPool::class)
    ->getQueryBuilderForTable('tx_recordmodules_config');
$configurationRecords = $qb->select('*')
    ->from('tx_recordmodules_config')
    ->executeQuery()->fetchAllAssociative();


$createCustomModuleGroup = false;
foreach ($GLOBALS['TCA'] as $table => $settings) {

    $settings = $settings['ctrl']['recordModule'] ?? [];
    if (!empty($settings)) {
        $result = registerModule($table, $settings, $modules);
        if ($result === true) {
            $createCustomModuleGroup = true;
        }
    }
}

foreach ($configurationRecords as $record) {
    $record['activate'] = true;
    $result = registerModule($record['tablename'], $record, $modules);
    if ($result === true) {
        $createCustomModuleGroup = true;
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
