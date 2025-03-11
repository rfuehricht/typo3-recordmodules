<?php

namespace Rfuehricht\Recordmodules\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Controller\Event\RenderAdditionalContentToRecordListEvent;
use TYPO3\CMS\Backend\Controller\RecordListController;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Module\ModuleInterface;
use TYPO3\CMS\Backend\RecordList\DatabaseRecordList;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Backend\Template\Components\Buttons\ButtonInterface;
use TYPO3\CMS\Backend\Template\Components\Buttons\GenericButton;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsController]
final class ModuleController extends RecordListController
{


    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {


        /** @var ModuleData moduleData */
        $this->moduleData = $request->getAttribute('moduleData');
        /** @var Route $route */
        $route = $request->getAttribute('route');

        /** @var ModuleInterface $module */
        $module = $route->getOption('module');

        //Do not allow override of default settings from pageTS or anywhere else
        $this->moduleData->set('table', $module->getDefaultModuleData()['table']);
        $this->moduleData->set('pids', $module->getDefaultModuleData()['pids']);

        $languageService = $this->getLanguageService();
        $backendUser = $this->getBackendUserAuthentication();
        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $perms_clause = $backendUser->getPagePermsClause(Permission::PAGE_SHOW);

        $currentTable = $this->moduleData->get('table');

        $this->id = (int)($parsedBody['id'] ?? $queryParams['id'] ?? '');
        $pids = [];
        if (is_array($this->moduleData->get('pids')) && !empty($this->moduleData->get('pids'))) {
            $pids = $this->moduleData->get('pids');
            foreach ($pids as &$pid) {
                $pid = intval($pid);
            }

            reset($pids);
        } elseif (!is_array($this->moduleData->get('pids')) && strlen(trim($this->moduleData->get('pids'))) > 0) {
            $pids = GeneralUtility::intExplode(',', (string)$this->moduleData->get('pids'), false);
            foreach ($pids as $idx => $pid) {
                if (strlen(trim($pid)) === 0) {
                    unset($pids[$idx]);
                }
            }
            reset($pids);
        } elseif ((isset($parsedBody['id']) && $parsedBody['id'] !== '') || (isset($queryParams['id']) && $queryParams['id'] !== '')) {
            $pids = [
                $this->id
            ];
        }

        unset($pid);


        foreach ($pids as $idx => $pid) {
            $pageInfo = BackendUtility::readPageAccess($pid, $perms_clause);

            $access = is_array($pageInfo);
            if (!$access) {
                unset($pids[$idx]);
            }
        }


        $this->pageRenderer->addInlineLanguageLabelFile('EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf');

        BackendUtility::lockRecords();

        if (!$this->id) {
            $this->id = reset($pids);
        }
        $pointer = max(0, (int)($parsedBody['pointer'] ?? $queryParams['pointer'] ?? 0));
        $this->table = (string)($currentTable);
        $this->searchTerm = trim((string)($parsedBody['searchTerm'] ?? $queryParams['searchTerm'] ?? ''));
        $search_levels = 0;
        $this->returnUrl = GeneralUtility::sanitizeLocalUrl((string)($parsedBody['returnUrl'] ?? $queryParams['returnUrl'] ?? ''));
        $cmd = (string)($parsedBody['cmd'] ?? $queryParams['cmd'] ?? '');
        $siteLanguages = $request->getAttribute('site')->getAvailableLanguages($this->getBackendUserAuthentication(), false, $this->id);

        $pageInfo = BackendUtility::readPageAccess($this->id, $perms_clause);
        $access = is_array($pageInfo);
        $this->pageInfo = is_array($pageInfo) ? $pageInfo : [];
        $this->pagePermissions = new Permission($backendUser->calcPerms($pageInfo));

        $view = $this->moduleTemplateFactory->create($request);

        $view->assignMultiple([
            'pageId' => $this->id,
            'table' => $this->table,
            'tabs' => []
        ]);

        if (empty($pids)) {
            $this->addFlashMessage($view, 'noPagesForThisTable', ContextualFeedbackSeverity::ERROR);
            return $view->renderResponse('List');

        }

        if (!$backendUser->isAdmin() && !$backendUser->check('tables_read', $currentTable)) {
            $this->addFlashMessage($view, 'noAccess', ContextualFeedbackSeverity::ERROR);
            return $view->renderResponse('List');
        }

        $this->allowSearch = true;


        $this->modTSconfig['searchLevel.'] = [
            'items.' => [
                '0' => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.0'
            ]
        ];

        // Overwrite to show search on search request
        if (!empty($this->searchTerm)) {
            $this->moduleData->set('searchBox', true);
        }

        $dbList = GeneralUtility::makeInstance(DatabaseRecordList::class);
        $dbList->setRequest($request);
        $dbList->setModuleData($this->moduleData);
        $dbList->calcPerms = $this->pagePermissions;
        $dbList->returnUrl = $this->returnUrl;
        $dbList->showClipboardActions = true;
        $dbList->disableSingleTableView = true;
        $dbList->listOnlyInSingleTableMode = false;

        // Only list current table
        $tablesToHide = array_keys($GLOBALS['TCA']);
        unset($tablesToHide[array_search($currentTable, $tablesToHide)]);
        $dbList->hideTables = implode(',', $tablesToHide);
        $dbList->allowedNewTables = [$currentTable];

        $dbList->hideTranslations = false;
        $dbList->tableTSconfigOverTCA = [];

        $dbList->pageRow = $this->pageInfo;
        $dbList->modTSconfig = $this->modTSconfig;
        $dbList->setLanguagesAllowedForUser($siteLanguages);

        $clipboard = $this->initializeClipboard($request, (bool)$this->moduleData->get('clipBoard'));
        $dbList->clipObj = $clipboard;

        $additionalRecordListEvent = $this->eventDispatcher->dispatch(new RenderAdditionalContentToRecordListEvent($request));


        $tableListHtml = '';
        if ($access || ($this->id === 0 && $this->searchTerm !== '')) {
            // If there is access to the page or root page is used for searching, then perform actions and render table list.
            if ($cmd === 'delete' && $request->getMethod() === 'POST') {
                $this->deleteRecords($request, $clipboard);
            }
            $dbList->start($this->id, $this->table, $pointer, $this->searchTerm, $search_levels);
            $tableListHtml = $dbList->generateList();
        }

        $title = $pageInfo['title'] ?? '';

        $searchBoxHtml = '';
        if ($this->allowSearch && $this->moduleData->get('searchBox') && ($tableListHtml || !empty($this->searchTerm))) {
            $searchBoxHtml = $this->renderSearchBox($request, $dbList, $this->searchTerm, $search_levels);
        }
        $clipboardHtml = '';
        if ($this->moduleData->get('clipBoard') && ($tableListHtml || $clipboard->hasElements())) {
            $clipboardHtml = '<hr class="spacer"><typo3-backend-clipboard-panel return-url="' . htmlspecialchars($dbList->listURL()) . '"></typo3-backend-clipboard-panel>';
        }

        if (empty($tableListHtml)) {
            $this->addNoRecordsFlashMessage($view, $this->table);
            $tableListHtml = $this->createActionButtonNewRecord($dbList->listURL());
        }
        if ($pageInfo) {
            $view->getDocHeaderComponent()->setMetaInformation($pageInfo);
        }

        $this->modTSconfig['noCreateRecordsLink'] = true;
        $this->getDocHeaderButtons($view, $clipboard, $request, $this->table, $dbList->listURL(), []);

        $tabs = [];
        foreach ($pids as $pid) {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            try {
                $site = $siteFinder->getSiteByPageId($pid);
                $identifier = $site->getIdentifier();

                if (!isset($tabs[$identifier])) {
                    $tabs[$identifier] = [];
                }
                if (!isset($tabs[$identifier][$pid])) {
                    $tabs[$identifier][$pid] = [];
                }
                $page = BackendUtility::getRecord('pages', $pid);
                $tabs[$identifier][$pid] = $page;

                $rootLine = BackendUtility::BEgetRootLine($pid, '', true);
                ksort($rootLine);
                $tabs[$identifier][$pid]['rootLine'] = $rootLine;

                if ($pid === $this->id) {
                    $tabs[$identifier][$pid]['active'] = true;
                }

                $tabs[$identifier][$pid]['url'] = $this->uriBuilder->buildUriFromRoutePath('/module/record/' . $this->table, ['id' => $pid]);
            } catch (SiteNotFoundException) {
            }

        }

        $tableTitle = $GLOBALS['TCA'][$this->table]['ctrl']['title'] ?? $this->table;
        if (str_starts_with($tableTitle, 'LLL:')) {
            $tableTitle = $languageService->sL($tableTitle);
        }
        $view->assignMultiple([
            'pageId' => $this->id,
            'table' => $this->table,
            'tabs' => $tabs,
            'pageTitle' => $title,
            'tableTitle' => $tableTitle,
            'additionalContentTop' => $additionalRecordListEvent->getAdditionalContentAbove(),
            'searchBoxHtml' => $searchBoxHtml,
            'tableListHtml' => $tableListHtml,
            'clipboardHtml' => $clipboardHtml,
            'additionalContentBottom' => $additionalRecordListEvent->getAdditionalContentBelow(),
        ]);
        return $view->renderResponse('List');
    }

    /**
     * @param ModuleTemplate $view
     * @param string $key
     * @param ContextualFeedbackSeverity $severity
     * @return void
     */
    protected function addFlashMessage(ModuleTemplate $view, string $key, ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::INFO): void
    {
        $languageService = $this->getLanguageService();
        $message = $languageService->sL('LLL:EXT:recordmodules/Resources/Private/Language/locallang_mod.xlf:' . $key);
        $view->addFlashMessage($message, '', $severity);
    }

    /**
     * If new records can be created on this page, create a button
     *
     * @param $returnUrl
     * @return ButtonInterface|null
     * @throws RouteNotFoundException
     */
    protected function createActionButtonNewRecord($returnUrl): ?ButtonInterface
    {
        $tag = 'a';
        $iconIdentifier = 'actions-plus';
        $label = $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_mod_web_list.xlf:new');
        $attributes = [
            'data-recordlist-action' => 'new',
        ];

        $attributes['href'] = $this->uriBuilder->buildUriFromRoute(
            'record_edit',
            [
                'edit' => [
                    $this->table => [
                        $this->id => 'new',
                    ],
                ],
                'returnUrl' => $returnUrl,
            ]
        );

        $button = GeneralUtility::makeInstance(GenericButton::class);
        $button->setTag($tag);
        $button->setLabel($label);
        $button->setShowLabelText(true);
        $button->setIcon($this->iconFactory->getIcon($iconIdentifier, Icon::SIZE_SMALL));
        $button->setAttributes($attributes);

        return $button;
    }

}