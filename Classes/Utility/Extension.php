<?php

namespace DrBlitz\GoogleIndexer\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Extension
{
    public static function isConfigFileExist(): bool
    {
        $configFile = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('google_indexer', 'config_file_path');
        return file_exists($configFile);
    }
}
