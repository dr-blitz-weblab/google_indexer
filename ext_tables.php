<?php

// Prevent Script from being called directly
defined('TYPO3') or die();

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
