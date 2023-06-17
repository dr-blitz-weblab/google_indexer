<?php

// Prevent Script from being called directly
use DrBlitz\GoogleIndexer\Hooks\ProcessCmdmap;

defined('TYPO3_MODE') || die('Access denied.');

// encapsulate all locally defined variables
(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'GoogleIndexer',
        'web', // Make module a submodule of 'Admin Tools'
        'googleIndexer', // Submodule key
        'after:termplate', // Position
        [
            \DrBlitz\GoogleIndexer\Controller\ModuleController::class => 'index,update,remove,checkStatus',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:google_indexer/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:google_indexer/Resources/Private/Language/Module/locallang_mod.xlf',
        ]
    );
})();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = ProcessCmdmap::class;
