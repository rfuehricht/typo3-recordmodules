<?php

declare(strict_types=1);

use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\BackendConfigurationManager;

$backendConfigurationManager = GeneralUtility::makeInstance(BackendConfigurationManager::class);
$tsSetup = $backendConfigurationManager->getTypoScriptSetup();

$settingsFromTypoScript = [];
if (isset($tsSetup['module.']['tx_recordmodules.'])) {
    $typoscriptService = GeneralUtility::makeInstance(TypoScriptService::class);
    $settingsFromTypoScript = $typoscriptService->convertTypoScriptArrayToPlainArray($tsSetup['module.']['tx_recordmodules.']);
}

$modules = [];
$extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['recordmodules'] ?? [];
if ($extensionConfiguration
    && isset($extensionConfiguration['tablesAndPids'])
    && strlen(trim($extensionConfiguration['tablesAndPids'])) > 0) {

    $extensionConfiguration['tables'] = [];

    $settingsParts = GeneralUtility::trimExplode('|', $extensionConfiguration['tablesAndPids'], true);
    foreach ($settingsParts as $setting) {
        $parts = GeneralUtility::trimExplode(':', $setting, true);
        $extensionConfiguration['tables'][$parts[0]] = [
            'activate' => 1,
            'pids' => $parts[1]
        ];
    }
    unset($extensionConfiguration['tablesAndPids']);
}


\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
    $extensionConfiguration,
    $settingsFromTypoScript,
    true
);


$tableSettingsFromExtensionConfiguration = $extensionConfiguration['tables'];

$addToWebModule = $extensionConfiguration['addToWebModule'] ?? false;

$parent = ($addToWebModule ? 'web' : 'recordmodules');

$extensionConfiguration['sorting'] = ($extensionConfiguration['sorting'] ? GeneralUtility::trimExplode(',', $extensionConfiguration['sorting']) : []);


$modulesToAdd = [];
foreach ($GLOBALS['TCA'] as $table => $settings) {

    $localSettings = $settings['ctrl']['recordModule'] ?? [];

    if (isset($tableSettingsFromExtensionConfiguration[$table])) {
        \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
            $localSettings,
            $tableSettingsFromExtensionConfiguration[$table],
            true
        );
    }


    if ((isset($localSettings['activate']) &&
        boolval($localSettings['activate']) === true)) {

        $typeIcons = $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'] ?? [];

        $sorting = array_search($table, $extensionConfiguration['sorting']);
        if ($sorting === false) {
            $sorting = 9999;
        }

        $localModuleConfiguration = [
            'parent' => $parent,
            'position' => ['before' => 'web_list'],
            'sorting' => intval($sorting),
            'access' => 'user',
            'workspaces' => 'live',
            'path' => '/module/record/' . $table,
            'labels' => [
                'title' => $GLOBALS['TCA'][$table]['ctrl']['title']
            ],
            'extensionName' => 'Recordmodules',
            'navigationComponent' => !isset($localSettings['pids']) ? '@typo3/backend/page-tree/page-tree-element' : '',

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

        if (isset($localSettings['title'])) {
            $localModuleConfiguration['labels']['title'] = $localSettings['title'];
        }

        if (isset($localSettings['icon'])) {
            $localModuleConfiguration['icon'] = $localSettings['icon'];
        } elseif (isset($localSettings['iconIdentifier'])) {
            $localModuleConfiguration['iconIdentifier'] = $localSettings['iconIdentifier'];
        } elseif (isset($GLOBALS['TCA'][$table]['ctrl']['iconIdentifier'])) {
            $localModuleConfiguration['iconIdentifier'] = $GLOBALS['TCA'][$table]['ctrl']['iconIdentifier'];
        } elseif (isset($GLOBALS['TCA'][$table]['ctrl']['icon'])) {
            $localModuleConfiguration['icon'] = $GLOBALS['TCA'][$table]['ctrl']['icon'];
        } elseif (isset($GLOBALS['TCA'][$table]['ctrl']['iconfile'])) {
            $localModuleConfiguration['icon'] = $GLOBALS['TCA'][$table]['ctrl']['iconfile'];
        } elseif (count($typeIcons) > 0) {
            $localModuleConfiguration['iconIdentifier'] = reset($typeIcons);
        }

        $modules['recordmodules_module_' . $table] = $localModuleConfiguration;
    }
}


if ($modules) {

    uasort($modules, function ($a, $b) {
        return $a['sorting'] <=> $b['sorting'];
    });

    if ($parent == 'recordmodules') {
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