<?php

namespace DrBlitz\GoogleIndexer\Utility;

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

    public static function isConfigFileExist(): bool
    {
        $configFile = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('google_indexer', 'config_file_path');
        return file_exists($configFile);
    }

    public static function getAllDokType(): array
    {
        $configFile = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('google_indexer', 'doktype');
        return explode(',', $configFile) ?? [1];
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
