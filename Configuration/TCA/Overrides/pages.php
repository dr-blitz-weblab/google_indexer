<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [
        'googleindexer_executetime' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:google_indexer/Resources/Private/Language/locallang_db.xlf:pages.googleindexer_executetime',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
            ],
        ],
        'googleindexer_last_api_answer' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:google_indexer/Resources/Private/Language/locallang_db.xlf:pages.googleindexer_last_api_answer',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
            ],
        ],
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--div--;LLL:EXT:google_indexer/Resources/Private/Language/locallang_db.xlf:pages.tabs.google_indexer,googleindexer_executetime,googleindexer_last_api_answer'
);
