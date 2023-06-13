<?php

use DrBlitz\GoogleIndexer\Controller\ModuleController;

return [
    'web_google_indxer' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/page/google_indexer',
        'labels' => 'LLL:EXT:google_indexer/Resources/Private/Language/Module/locallang_mod.xlf',
        'extensionName' => 'GoogleIndexer',
        'iconIdentifier' => 'weblab-google-indexer',
        'inheritNavigationComponentFromMainModule' => true,
        'controllerActions' => [
            ModuleController::class => [
                'index', 'update', 'remove', 'checkStatus',
            ],
        ],
    ],
];
