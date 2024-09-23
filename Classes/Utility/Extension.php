<?php

namespace DrBlitz\GoogleIndexer\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Extension
{
    private const TABLE_NAME = 'pages';

    public static function isConfigFileExist(int $pageId): bool
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return file_exists($siteFinder->getSiteByPageId($pageId)->getConfiguration()['google_api_key_path']);
    }

    public static function getConfigFile(int $pageId): string
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getSiteByPageId($pageId)->getConfiguration()['google_api_key_path'];
    }

    public static function getAllDokType(): array
    {
        try {
            $configFile = GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get('google_indexer', 'doktype');
        } catch (
            ExtensionConfigurationPathDoesNotExistException |
            ExtensionConfigurationExtensionNotConfiguredException $exception
        ) {
            return [1];
        }

        return explode(',', $configFile);
    }

    public static function getFrontendUrl(int $uid, int $language = 0): string
    {
        $siteFinder  = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($uid);
        $router = $site->getRouter();
        return (string)$router->generateUri(
            $uid,
            [
                '_language' => $language,
            ],
            '',
            RouterInterface::ABSOLUTE_URL
        );
    }
    public static function getPage(int $uid, array $dokTypes): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        $result = $queryBuilder
            ->select('uid', 'title', 'hidden', 'sys_language_uid', 'googleindexer_executetime', 'googleindexer_last_api_answer')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->in('doktype', $queryBuilder->createNamedParameter($dokTypes, Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )->orWhere(
                $queryBuilder->expr()->eq('l10n_parent', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
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

}
