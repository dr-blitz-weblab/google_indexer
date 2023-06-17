<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace DrBlitz\GoogleIndexer\Controller;

use Doctrine\DBAL\Exception;
use DrBlitz\GoogleIndexer\Enumeration\GoogleApi;
use DrBlitz\GoogleIndexer\Service\GoogleIndexingApi;
use DrBlitz\GoogleIndexer\Utility\Extension;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ModuleController extends ActionController
{
    use LoggerAwareTrait;
    private const TABLE_NAME = 'pages';
    private int $pageUid;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory
    ) {
    }

    /**
     * @throws Exception
     */
    public function indexAction(): ResponseInterface
    {
        $isConfigFileExist = Extension::isConfigFileExist();
        if (!$isConfigFileExist) {
            return new ForwardResponse('missingSetup');
        }
        $this->addFlashMessage(
            LocalizationUtility::translate('welcome', 'google_indexer'),
            LocalizationUtility::translate('header', 'google_indexer'),
            ContextualFeedbackSeverity::NOTICE
        );

        $view = $this->initializeModuleTemplate($this->request);
        $view->assignMultiple(
            [
                'records' => Extension::getPage($this->pageUid),
        ]
        );
        return $view->renderResponse();
    }

    public function missingSetupAction(): ResponseInterface
    {
        $this->addFlashMessage(
            LocalizationUtility::translate('missing_config_ile', 'google_indexer'),
            LocalizationUtility::translate('header', 'google_indexer'),
            ContextualFeedbackSeverity::ERROR
        );
        $view = $this->initializeModuleTemplate($this->request);
        return $view->renderResponse();
    }

    public function updateAction(int $language = 0): ResponseInterface
    {
        $url = $this->getFrontendUrl($language);
        $googleApi =GeneralUtility::makeInstance(GoogleIndexingApi::class);
        $type = GoogleApi::cast('URL_UPDATED');
        $response = $googleApi->execute($url, $type);

        $severity = ContextualFeedbackSeverity::OK;
        if ($response['status'] !== 200) {
            $severity = ContextualFeedbackSeverity::ERROR;
        }
        $this->addFlashMessage(
            $response['message'],
            LocalizationUtility::translate('header', 'google_indexer'),
            $severity
        );
        if ($response['status'] === 200) {
            $this->saveApiAnswer($type->__toString(), $language);
        }

        return new ForwardResponse('index');
    }

    public function removeAction(int $language = 0): ResponseInterface
    {
        $url = $this->getFrontendUrl($language);
        $googleApi =GeneralUtility::makeInstance(GoogleIndexingApi::class);
        $type = GoogleApi::cast('URL_DELETED');
        $response = $googleApi->execute($url, $type);

        $severity = ContextualFeedbackSeverity::OK;
        if ($response['status'] !== 200) {
            $severity = ContextualFeedbackSeverity::ERROR;
        }
        $this->addFlashMessage(
            $response['message'],
            LocalizationUtility::translate('header', 'google_indexer'),
            $severity
        );
        if ($response['status'] === 200) {
            $this->saveApiAnswer($type->__toString(), $language);
        }
        return new ForwardResponse('index');
    }

    public function checkStatusAction(int $language = 0): ForwardResponse
    {
        $url = $this->getFrontendUrl($language);
        $googleApi =GeneralUtility::makeInstance(GoogleIndexingApi::class);
        $response = $googleApi->getNotificationStatus($url);
        $severity = ContextualFeedbackSeverity::OK;
        if ($response['status'] !== 200) {
            $severity = ContextualFeedbackSeverity::ERROR;
        }
        $this->addFlashMessage(
            $response['message'],
            LocalizationUtility::translate('header', 'google_indexer'),
            $severity
        );
        return new ForwardResponse('index');
    }

    protected function getFrontendUrl(int $language): string
    {
        $siteFinder  = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($this->pageUid);
        $router = $site->getRouter();
        return (string)$router->generateUri(
            $this->pageUid,
            [
                '_language' => $language,
            ],
            '',
            RouterInterface::ABSOLUTE_URL
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getPage(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $result = $queryBuilder
            ->select('uid', 'title', 'hidden', 'sys_language_uid', 'googleindexer_executetime', 'googleindexer_last_api_answer')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($this->pageUid, \PDO::PARAM_INT))
            )->orWhere(
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($this->pageUid, \PDO::PARAM_INT))
            )->orderBy(
                'sys_language_uid',
                'ASC'
            )
            ->executeQuery();
        $results = [];
        while ($row = $result->fetchAssociative()) {
            $results[] = $row;
        }
        return $results;
    }

    /**
     * @param string $text
     * @param int $language
     */
    private function saveApiAnswer(string $text, int $language): void
    {
        $dateTime = new \DateTime();
        $data = [
            'googleindexer_executetime' => $dateTime->getTimestamp(),
            'googleindexer_last_api_answer' => $text,
        ];

        if ($language) {
            $where = ['l10n_parent' => $this->pageUid, 'sys_language_uid' => $language];
        } else {
            $where =['uid' => $this->pageUid, 'sys_language_uid' => $language];
        }
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages')
            ->update(
                'pages',
                $data,
                $where
            );
    }

    /**
     * Generates the action menu
     */
    protected function initializeModuleTemplate(
        ServerRequestInterface $request
    ): ModuleTemplate {
        $view = $this->moduleTemplateFactory->create($request);
        $permissionClause = $this->getBackendUserAuthentication()->getPagePermsClause(Permission::PAGE_SHOW);
        $pageRecord = BackendUtility::readPageAccess(
            $this->pageUid,
            $permissionClause
        );
        if ($pageRecord) {
            $view->getDocHeaderComponent()->setMetaInformation($pageRecord);
        }
        $view->setFlashMessageQueue($this->getFlashMessageQueue());

        return $view;
    }

    /**
     * Function will be called before every other action
     */
    protected function initializeAction(): void
    {
        $this->pageUid = (int)($this->request->getQueryParams()['id'] ?? 0);
        parent::initializeAction();
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
