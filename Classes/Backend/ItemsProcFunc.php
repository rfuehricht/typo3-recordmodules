<?php

namespace Rfuehricht\Recordmodules\Backend;

use TYPO3\CMS\Backend\Module\Module;
use TYPO3\CMS\Backend\Module\ModuleProvider;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemsProcFunc
{

    public function getAllTables(array &$params): void
    {
        $params['items'][] =
            [
                'value' => '',
                'label' => ''
            ];
        foreach ($GLOBALS['TCA'] as $table => $settings) {
            $icon = $settings['ctrl']['iconfile'] ?? '';
            if (!$icon && isset($settings['ctrl']['typeicon_classes'])) {
                if (isset($settings['ctrl']['typeicon_classes']['default'])) {
                    $icon = $settings['ctrl']['typeicon_classes']['default'];
                } else {
                    $icon = reset($settings['ctrl']['typeicon_classes']);
                }
            }

            $params['items'][] = ['value' => $table, 'label' => $settings['ctrl']['title'], 'icon' => $icon];
        }

    }

    public function getAllToplevelModules(array &$params): void
    {
        $modules = GeneralUtility::makeInstance(ModuleProvider::class)
            ->getModules();


        $params['items'][] =
            [
                'value' => '',
                'label' => ''
            ];
        foreach ($modules as $module) {
            /** @var Module $module */
            $params['items'][] = [
                'value' => $module->getIdentifier(),
                'label' => $module->getTitle(),
                'icon' => $module->getIconIdentifier()
            ];
        }

    }
}